<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private $serverKey;
    private $senderId;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
        $this->senderId = config('services.fcm.sender_id');
    }

    public function sendNotification($title, $body, $data = [], $userIds = null)
    {
        $query = FcmToken::whereNotNull('token');
        
        // If specific user IDs provided, send only to those users
        if ($userIds !== null) {
            if (is_array($userIds)) {
                $query->whereIn('user_id', $userIds);
            } else {
                $query->where('user_id', $userIds);
            }
        }
        
        $tokens = $query->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::warning('No FCM tokens found', ['user_ids' => $userIds]);
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ]);

        if ($response->successful()) {
            Log::info('FCM notification sent successfully', ['response' => $response->json(), 'tokens_count' => count($tokens)]);
            return true;
        }

        Log::error('FCM notification failed', ['response' => $response->body()]);
        return false;
    }

    public function sendToUser($userId, $title, $body, $data = [])
    {
        return $this->sendNotification($title, $body, $data, $userId);
    }

    public function sendToUsers(array $userIds, $title, $body, $data = [])
    {
        return $this->sendNotification($title, $body, $data, $userIds);
    }

    public function registerToken($userId, $token, $deviceId = null)
    {
        return FcmToken::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $userId,
                'device_id' => $deviceId
            ]
        );
    }
}

