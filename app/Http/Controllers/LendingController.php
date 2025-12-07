<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LendingController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request): JsonResponse
    {
        $lendings = Lending::where('user_id', $request->user()->id)
            ->orderBy('expected_return_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($lendings);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'person_name' => 'required|string|max:255',
            'item_type' => 'required|in:money,item',
            'amount' => 'required_if:item_type,money|nullable|numeric|min:0',
            'item_description' => 'required_if:item_type,item|nullable|string|max:255',
            'date_given' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:date_given',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $lending = Lending::create($data);

        // Send notification
        $itemType = $lending->item_type === 'money' 
            ? '$' . number_format($lending->amount, 2)
            : $lending->item_description;
        
        $this->fcmService->sendToUser(
            $request->user()->id,
            'Lending Recorded',
            "Lent {$itemType} to {$lending->person_name}" . ($lending->expected_return_date ? " (Expected return: " . \Carbon\Carbon::parse($lending->expected_return_date)->format('M d, Y') . ")" : ''),
            ['type' => 'lending_created', 'lending_id' => $lending->id]
        );

        return response()->json($lending, 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $lending = Lending::where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json($lending);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $lending = Lending::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'person_name' => 'sometimes|required|string|max:255',
            'item_type' => 'sometimes|required|in:money,item',
            'amount' => 'required_if:item_type,money|nullable|numeric|min:0',
            'item_description' => 'required_if:item_type,item|nullable|string|max:255',
            'date_given' => 'sometimes|required|date',
            'expected_return_date' => 'sometimes|required|date',
            'actual_return_date' => 'nullable|date',
            'is_returned' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lending->update($request->all());

        if ($request->has('is_returned') && $request->is_returned && !$lending->actual_return_date) {
            $lending->actual_return_date = now();
            $lending->save();
            
            // Send return notification
            $itemType = $lending->item_type === 'money' 
                ? '$' . number_format($lending->amount, 2)
                : $lending->item_description;
            
            $this->fcmService->sendToUser(
                $request->user()->id,
                'Item Returned',
                "{$lending->person_name} has returned {$itemType}",
                ['type' => 'lending_returned', 'lending_id' => $lending->id]
            );
        }

        return response()->json($lending);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $lending = Lending::where('user_id', $request->user()->id)->findOrFail($id);
        $lending->delete();

        return response()->json(['message' => 'Lending deleted successfully']);
    }

    public function markReturned(Request $request, $id): JsonResponse
    {
        $lending = Lending::where('user_id', $request->user()->id)->findOrFail($id);
        $lending->is_returned = true;
        $lending->actual_return_date = now();
        $lending->save();

        // Send return notification
        $itemType = $lending->item_type === 'money' 
            ? '$' . number_format($lending->amount, 2)
            : $lending->item_description;
        
        $this->fcmService->sendToUser(
            $request->user()->id,
            'Item Returned',
            "{$lending->person_name} has returned {$itemType}",
            ['type' => 'lending_returned', 'lending_id' => $lending->id]
        );

        return response()->json($lending);
    }

    public function checkDueReturns(): JsonResponse
    {
        $today = Carbon::today();
        $dueLendings = Lending::where('is_returned', false)
            ->where('expected_return_date', '<=', $today)
            ->with('user')
            ->get();

        foreach ($dueLendings as $lending) {
            $itemType = $lending->item_type === 'money' 
                ? '$' . number_format($lending->amount, 2)
                : $lending->item_description;

            $daysOverdue = Carbon::parse($lending->expected_return_date)->diffInDays($today);
            $title = $daysOverdue > 0 ? 'Overdue Return Reminder' : 'Return Reminder';
            $message = $daysOverdue > 0 
                ? "Overdue by {$daysOverdue} day(s): {$lending->person_name} was supposed to return {$itemType} on {$lending->expected_return_date->format('M d, Y')}"
                : "Reminder: {$lending->person_name} should return {$itemType} today";

            $this->fcmService->sendToUser(
                $lending->user_id,
                $title,
                $message,
                ['type' => 'lending_reminder', 'lending_id' => $lending->id]
            );
        }

        return response()->json([
            'message' => 'Checked due returns',
            'count' => $dueLendings->count()
        ]);
    }
}

