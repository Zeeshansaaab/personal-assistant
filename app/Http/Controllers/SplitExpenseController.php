<?php

namespace App\Http\Controllers;

use App\Models\SplitExpense;
use App\Models\Group;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SplitExpenseController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request, $groupId): JsonResponse
    {
        $group = Group::findOrFail($groupId);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenses = SplitExpense::where('group_id', $groupId)
            ->with(['payer', 'group.members'])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Transform expenses to include splits
        $expenses = $expenses->map(function ($expense) use ($group) {
            return $this->transformExpense($expense, $group);
        });

        return response()->json($expenses);
    }

    public function store(Request $request, $groupId): JsonResponse
    {
        $group = Group::findOrFail($groupId);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'paid_by' => 'sometimes|exists:users,id',
            'split_type' => 'sometimes|in:equal,unequal,percentage',
            'split_details' => 'required_if:split_type,unequal|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate split_details for unequal splits
        if ($request->split_type === 'unequal') {
            $totalSplit = array_sum($request->split_details);
            if (abs($totalSplit - $request->amount) > 0.01) {
                return response()->json(['errors' => ['split_details' => ['Sum of split amounts must equal total amount']]], 422);
            }
        }

        // Validate that paid_by is a member of the group
        $paidBy = $request->paid_by ?? $request->user()->id;
        if (!$group->isMember($paidBy)) {
            return response()->json(['errors' => ['paid_by' => ['The person who paid must be a member of the group']]], 422);
        }

        $expense = SplitExpense::create([
            'group_id' => $groupId,
            'paid_by' => $paidBy,
            'amount' => $request->amount,
            'description' => $request->description,
            'expense_date' => $request->expense_date,
            'split_type' => $request->split_type ?? 'equal',
            'split_details' => $request->split_details ?? null,
        ]);

        $expense->load(['payer', 'group.members']);

        // Transform the response to include splits
        $expense = $this->transformExpense($expense, $group);

        // Send notifications to all group members except the payer
        $payer = \App\Models\User::find($paidBy);
        $memberIds = $group->members->pluck('id')->reject(fn($id) => $id === $paidBy)->toArray();
        
        if (!empty($memberIds) && $payer) {
            $currency = $payer->currency ?? 'PKR';
            $symbols = ['PKR' => '₨', 'INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
            $symbol = $symbols[$currency] ?? '₨';
            
            $this->fcmService->sendToUsers(
                $memberIds,
                'New Group Expense',
                "{$payer->name} added an expense: {$expense['description']} ({$symbol}" . number_format($expense['amount'], 2) . ")",
                ['type' => 'group_expense_added', 'expense_id' => $expense['id'], 'group_id' => $groupId]
            );
        }

        return response()->json($expense, 201);
    }

    public function update(Request $request, $groupId, $expenseId): JsonResponse
    {
        $group = Group::findOrFail($groupId);
        $expense = SplitExpense::where('group_id', $groupId)->findOrFail($expenseId);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow updating is_settled for now
        if ($request->has('is_settled')) {
            $expense->is_settled = $request->is_settled;
            $expense->save();
        }

        $expense->load(['payer', 'group.members']);
        $expense = $this->transformExpense($expense, $group);

        return response()->json($expense);
    }

    private function transformExpense($expense, $group)
    {
        $splits = [];
        $members = $group->members;
        
        if ($expense->split_type === 'equal') {
            $perPerson = $members->count() > 0 ? $expense->amount / $members->count() : 0;
            foreach ($members as $member) {
                $splits[] = [
                    'id' => $member->id,
                    'user_id' => $member->id,
                    'user_name' => $member->name ?? ($member->email ?? 'Unknown'),
                    'amount' => $perPerson,
                ];
            }
        } elseif ($expense->split_type === 'unequal' && $expense->split_details) {
            foreach ($expense->split_details as $userId => $amount) {
                $member = $members->firstWhere('id', $userId);
                if ($member) {
                    $splits[] = [
                        'id' => $member->id,
                        'user_id' => $member->id,
                        'user_name' => $member->name ?? ($member->email ?? 'Unknown'),
                        'amount' => $amount,
                    ];
                }
            }
        }

        return [
            'id' => $expense->id,
            'group_id' => $expense->group_id,
            'paid_by' => $expense->paid_by,
            'payer' => $expense->payer,
            'amount' => $expense->amount,
            'description' => $expense->description,
            'expense_date' => $expense->expense_date,
            'split_type' => $expense->split_type,
            'is_settled' => $expense->is_settled ?? false,
            'splits' => $splits,
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
        ];
    }

    public function getUserBalance(Request $request, $groupId): JsonResponse
    {
        $group = Group::findOrFail($groupId);
        $userId = $request->user()->id;

        if (!$group->isMember($userId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenses = SplitExpense::where('group_id', $groupId)->get();
        $members = $group->members;

        $paid = 0;
        $owed = 0;

        foreach ($expenses as $expense) {
            $userPaidForThis = $expense->paid_by === $userId;

            // If user paid for this expense, add to paid
            if ($userPaidForThis) {
                $paid += $expense->amount;
            }

            // Calculate how much user owes from splits
            // Only count if user didn't pay for this expense (they don't owe their own share)
            if (!$userPaidForThis) {
                if ($expense->split_type === 'equal') {
                    $perPerson = $members->count() > 0 ? $expense->amount / $members->count() : 0;
                    $owed += $perPerson;
                } elseif ($expense->split_type === 'unequal' && $expense->split_details) {
                    $userShare = $expense->split_details[$userId] ?? 0;
                    $owed += $userShare;
                }
            }
        }

        $net = $paid - $owed;

        return response()->json([
            'paid' => round($paid, 2),
            'owed' => round($owed, 2),
            'net' => round($net, 2),
        ]);
    }

    public function getSettlements(Request $request, $groupId): JsonResponse
    {
        $group = Group::findOrFail($groupId);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenses = SplitExpense::where('group_id', $groupId)->get();
        $members = $group->members;

        $balances = [];
        foreach ($members as $member) {
            $balances[$member->id] = [
                'user' => $member,
                'paid' => 0,
                'owed' => 0,
                'net' => 0,
            ];
        }

        foreach ($expenses as $expense) {
            $payerId = $expense->paid_by;
            $balances[$payerId]['paid'] += $expense->amount;

            if ($expense->split_type === 'equal') {
                $perPerson = $expense->amount / $members->count();
                foreach ($members as $member) {
                    if ($member->id != $payerId) {
                        $balances[$member->id]['owed'] += $perPerson;
                    }
                }
            } elseif ($expense->split_type === 'unequal' && $expense->split_details) {
                foreach ($expense->split_details as $userId => $amount) {
                    if ($userId != $payerId) {
                        $balances[$userId]['owed'] += $amount;
                    }
                }
            }
        }

        // Calculate net
        foreach ($balances as $userId => &$balance) {
            $balance['net'] = $balance['paid'] - $balance['owed'];
        }

        // Generate settlements (who owes whom)
        $settlements = [];
        $creditors = [];
        $debtors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance['net'] > 0.01) {
                $creditors[] = ['user_id' => $userId, 'amount' => $balance['net'], 'user' => $balance['user']];
            } elseif ($balance['net'] < -0.01) {
                $debtors[] = ['user_id' => $userId, 'amount' => abs($balance['net']), 'user' => $balance['user']];
            }
        }

        // Match creditors with debtors
        foreach ($creditors as &$creditor) {
            foreach ($debtors as &$debtor) {
                if ($debtor['amount'] > 0.01 && $creditor['amount'] > 0.01) {
                    $settleAmount = min($creditor['amount'], $debtor['amount']);
                    $settlements[] = [
                        'from' => $debtor['user'],
                        'to' => $creditor['user'],
                        'amount' => round($settleAmount, 2),
                    ];
                    $creditor['amount'] -= $settleAmount;
                    $debtor['amount'] -= $settleAmount;
                }
            }
        }

        return response()->json([
            'balances' => array_values($balances),
            'settlements' => $settlements,
        ]);
    }
}

