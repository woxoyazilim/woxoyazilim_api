<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);

        $msgBase = DB::table('messages')->where('status', '!=', 'deleted');

        return response()->json([
            'messages' => [
                'total' => (clone $msgBase)->count(),
                'new' => (clone $msgBase)->where('status', 'new')->count(),
                'in_progress' => (clone $msgBase)->whereIn('status', ['in_progress', 'waiting_info', 'waiting_customer', 'follow_up', 'read'])->count(),
                'quoted' => (clone $msgBase)->whereIn('status', ['quoted', 'meeting_scheduled', 'demo_scheduled', 'qualified', 'negotiation'])->count(),
                'won' => (clone $msgBase)->where('status', 'won')->count(),
                'lost' => (clone $msgBase)->where('status', 'lost')->count(),
                'today' => (clone $msgBase)->whereDate('created_at', $today)->count(),
                'this_week' => (clone $msgBase)->where('created_at', '>=', $weekAgo)->count(),
                'unassigned' => (clone $msgBase)->whereNull('assigned_to')->whereNotIn('status', ['archived', 'spam', 'won', 'lost'])->count(),
                'follow_ups_due' => (clone $msgBase)->whereNotNull('follow_up_at')->where('follow_up_at', '<=', $today->endOfDay())->whereNotIn('status', ['won', 'lost', 'archived', 'spam', 'deleted'])->count(),
                'erp_failed' => (clone $msgBase)->where('erp_sync_status', 'failed')->count(),
            ],
            'demoRequests' => [
                'total' => DB::table('demo_requests')->count(),
                'pending' => DB::table('demo_requests')->where('status', 'pending')->count(),
                'today' => DB::table('demo_requests')->whereDate('created_at', $today)->count(),
            ],
            'chatLeads' => [
                'total' => DB::table('chat_leads')->count(),
                'today' => DB::table('chat_leads')->whereDate('created_at', $today)->count(),
            ],
            'references' => [
                'total' => DB::table('references_portfolio')->count(),
            ],
            'services' => [
                'total' => DB::table('services')->count(),
            ],
        ]);
    }

    public function trends(Request $request)
    {
        $period = (int) $request->input('period', 7);
        $period = in_array($period, [7, 30]) ? $period : 7;
        $startDate = Carbon::today()->subDays($period - 1);

        $messages = DB::table('messages')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $demos = DB::table('demo_requests')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chatLeads = DB::table('chat_leads')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill all dates
        $result = ['messages' => [], 'demoRequests' => [], 'chatLeads' => []];
        for ($i = 0; $i < $period; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $result['messages'][] = ['date' => $date, 'count' => $messages[$date]->count ?? 0];
            $result['demoRequests'][] = ['date' => $date, 'count' => $demos[$date]->count ?? 0];
            $result['chatLeads'][] = ['date' => $date, 'count' => $chatLeads[$date]->count ?? 0];
        }

        return response()->json(['period' => $period, ...$result]);
    }

    public function personnel()
    {
        $users = DB::table('users')
            ->where('is_active', true)
            ->whereIn('role', ['super_admin', 'admin', 'sales', 'support'])
            ->get();

        $personnel = $users->map(function ($user) {
            $assigned = DB::table('messages')->where('assigned_to', $user->id)->count();
            $resolved = DB::table('messages')->where('assigned_to', $user->id)->whereIn('status', ['won', 'replied', 'archived'])->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'assigned' => $assigned,
                'resolved' => $resolved,
                'active' => $assigned - $resolved,
            ];
        });

        return response()->json(['personnel' => $personnel]);
    }
}
