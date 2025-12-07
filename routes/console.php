<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\LendingController;
use App\Services\FcmService;
use App\Models\Task;
use App\Models\Lending;
use Carbon\Carbon;

Schedule::call(function () {
    $lendingController = app(LendingController::class);
    $lendingController->checkDueReturns();
})->daily();

Schedule::call(function () {
    $today = Carbon::today();
    $tasks = Task::where('is_completed', false)
        ->where(function ($query) use ($today) {
            $query->where('due_date', $today)
                ->orWhere(function ($q) use ($today) {
                    $q->where('daily_reminder', true);
                });
        })
        ->with('user')
        ->get();

    $fcmService = app(FcmService::class);
    foreach ($tasks as $task) {
        $fcmService->sendToUser(
            $task->user_id,
            'Task Reminder',
            $task->title . ($task->due_date ? ' - Due: ' . Carbon::parse($task->due_date)->format('M d, Y') : ''),
            ['type' => 'task_reminder', 'task_id' => $task->id]
        );
    }
})->daily();

Schedule::call(function () {
    $today = Carbon::today();
    if ($today->isWeekend()) {
        $tasks = Task::where('is_completed', false)
            ->where('weekend_reminder', true)
            ->with('user')
            ->get();

        $fcmService = app(FcmService::class);
        foreach ($tasks as $task) {
            $fcmService->sendToUser(
                $task->user_id,
                'Weekend Task Reminder',
                $task->title,
                ['type' => 'task_reminder', 'task_id' => $task->id]
            );
        }
    }
})->weeklyOn(0, '8:00');

