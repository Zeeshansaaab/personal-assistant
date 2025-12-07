<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Expense::where('user_id', $request->user()->id);

        if ($request->has('month')) {
            $month = $request->month;
            $query->whereMonth('expense_date', Carbon::parse($month)->month)
                ->whereYear('expense_date', Carbon::parse($month)->year);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $expense = Expense::create($data);

        // Send notification
        $currency = $request->user()->currency ?? 'PKR';
        $symbols = ['PKR' => '₨', 'INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol = $symbols[$currency] ?? '₨';
        
        $this->fcmService->sendToUser(
            $request->user()->id,
            'Expense Added',
            "Added expense: {$expense->category} - {$symbol}" . number_format($expense->amount, 2),
            ['type' => 'expense_created', 'expense_id' => $expense->id]
        );

        return response()->json($expense, 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $expense = Expense::where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $expense = Expense::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->update($request->all());

        return response()->json($expense);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $expense = Expense::where('user_id', $request->user()->id)->findOrFail($id);
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }

    public function monthlySummary(Request $request): JsonResponse
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $summary = Expense::where('user_id', $request->user()->id)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('expense_date', Carbon::parse($month)->month)
            ->whereYear('expense_date', Carbon::parse($month)->year)
            ->groupBy('category')
            ->get();

        $total = Expense::where('user_id', $request->user()->id)
            ->whereMonth('expense_date', Carbon::parse($month)->month)
            ->whereYear('expense_date', Carbon::parse($month)->year)
            ->sum('amount');

        return response()->json([
            'month' => $month,
            'total' => $total,
            'by_category' => $summary,
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $months = $request->input('months', 6);
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $data = Expense::where('user_id', $request->user()->id)
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('expense_date', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($data);
    }
}

