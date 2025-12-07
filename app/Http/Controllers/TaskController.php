<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'repeat_type' => 'nullable|in:none,weekly,monthly',
            'daily_reminder' => 'boolean',
            'weekend_reminder' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $task = Task::create($data);

        // Send notification
        $this->fcmService->sendToUser(
            $request->user()->id,
            'Task Created',
            "Task '{$task->title}' has been created" . ($task->due_date ? " (Due: " . \Carbon\Carbon::parse($task->due_date)->format('M d, Y') . ")" : ''),
            ['type' => 'task_created', 'task_id' => $task->id]
        );

        return response()->json($task, 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $task = Task::where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $task = Task::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'repeat_type' => 'nullable|in:none,weekly,monthly',
            'daily_reminder' => 'boolean',
            'weekend_reminder' => 'boolean',
            'is_completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->all());

        if ($request->has('is_completed') && $request->is_completed) {
            $task->completed_at = now();
            $task->save();
            
            // Send completion notification
            $this->fcmService->sendToUser(
                $request->user()->id,
                'Task Completed',
                "Task '{$task->title}' has been marked as completed",
                ['type' => 'task_completed', 'task_id' => $task->id]
            );
        } elseif ($request->has('is_completed') && !$request->is_completed) {
            $task->completed_at = null;
            $task->save();
        }

        return response()->json($task);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $task = Task::where('user_id', $request->user()->id)->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}

