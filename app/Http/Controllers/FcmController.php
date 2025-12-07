<?php

namespace App\Http\Controllers;

use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FcmController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function registerToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fcmToken = $this->fcmService->registerToken(
            $request->user()->id,
            $request->token,
            $request->device_id
        );

        return response()->json($fcmToken, 201);
    }
}

