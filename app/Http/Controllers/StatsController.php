<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'references' => DB::table('references_portfolio')->count(),
            'services' => DB::table('services')->count(),
            'messages' => DB::table('messages')->count(),
            'demoRequests' => DB::table('demo_requests')->count(),
            'chatLeads' => DB::table('chat_leads')->count(),
        ]);
    }
}
