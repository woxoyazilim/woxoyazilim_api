<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\MessageActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['assignedMessages'])->orderBy('name')->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => $u->is_active,
                'last_login_at' => $u->last_login_at,
                'assigned_messages_count' => $u->assigned_messages_count,
            ]);

        return response()->json(['users' => $users]);
    }

    public function show(int $id)
    {
        $user = User::withCount(['assignedMessages'])->find($id);
        if (!$user) return response()->json(['error' => 'Kullanıcı bulunamadı'], 404);

        // Performance stats
        $resolved = Message::where('assigned_to', $id)->whereIn('status', ['won', 'replied', 'archived'])->count();
        $totalAssigned = Message::where('assigned_to', $id)->count();

        return response()->json([
            'user' => $user,
            'stats' => [
                'assigned' => $totalAssigned,
                'resolved' => $resolved,
                'active' => $totalAssigned - $resolved,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Kullanıcı bulunamadı'], 404);

        $request->validate([
            'role' => 'nullable|in:super_admin,admin,sales,support,viewer',
            'is_active' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        if ($request->has('role')) $user->role = $request->role;
        if ($request->has('is_active')) $user->is_active = $request->is_active;
        if ($request->has('name')) $user->name = $request->name;
        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function assignableUsers()
    {
        $users = User::where('is_active', true)
            ->whereIn('role', ['super_admin', 'admin', 'sales', 'support'])
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        return response()->json(['users' => $users]);
    }
}
