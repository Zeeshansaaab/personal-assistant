<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $groups = $user->groups()->with(['creator', 'members'])->get();

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        // Add creator as owner
        $group->members()->attach($request->user()->id, ['role' => 'owner']);

        // Add other members
        if ($request->member_ids) {
            foreach ($request->member_ids as $memberId) {
                if ($memberId != $request->user()->id) {
                    $group->members()->attach($memberId, ['role' => 'member']);
                    
                    // Send notification to new member
                    $this->fcmService->sendToUser(
                        $memberId,
                        'Added to Group',
                        "You have been added to the group '{$group->name}' by {$request->user()->name}",
                        ['type' => 'group_member_added', 'group_id' => $group->id]
                    );
                }
            }
        }

        $group->load(['creator', 'members']);

        return response()->json($group, 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $group = Group::with(['creator', 'members', 'splitExpenses.payer'])->findOrFail($id);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($group);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $group = Group::findOrFail($id);

        if ($group->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Only group owner can update'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $group->update($request->only(['name', 'description']));

        return response()->json($group);
    }

    public function addMember(Request $request, $id): JsonResponse
    {
        $group = Group::findOrFail($id);

        if (!$group->isMember($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($group->isMember($request->user_id)) {
            return response()->json(['message' => 'User is already a member'], 400);
        }

        $group->members()->attach($request->user_id, ['role' => 'member']);

        // Send notification to new member
        $this->fcmService->sendToUser(
            $request->user_id,
            'Added to Group',
            "You have been added to the group '{$group->name}' by {$request->user()->name}",
            ['type' => 'group_member_added', 'group_id' => $group->id]
        );

        return response()->json(['message' => 'Member added successfully']);
    }

    public function removeMember(Request $request, $id, $userId): JsonResponse
    {
        $group = Group::findOrFail($id);

        if ($group->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Only group owner can remove members'], 403);
        }

        if ($userId == $group->created_by) {
            return response()->json(['message' => 'Cannot remove group owner'], 400);
        }

        $group->members()->detach($userId);

        return response()->json(['message' => 'Member removed successfully']);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $users = User::where('email', 'like', "%{$query}%")
            ->orWhere('mobile', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->where('id', '!=', $request->user()->id)
            ->limit(10)
            ->get(['id', 'name', 'email', 'mobile']);

        return response()->json($users);
    }
}

