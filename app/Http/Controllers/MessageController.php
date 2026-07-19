<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\GeoIpService;
use App\Services\MessageService;
use App\Services\ErpSyncService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService,
        protected NotificationService $notificationService,
    ) {}

    /**
     * List messages with filtering, search, sorting, and pagination
     */
    public function index(Request $request)
    {
        $query = Message::with(['assignee:id,name,email']);

        // Filters
        if ($status = $request->input('status')) {
            $query->byStatus($status);
        }
        if ($priority = $request->input('priority')) {
            $query->byPriority($priority);
        }
        if ($assignee = $request->input('assigned_to')) {
            if ($assignee === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->byAssignee($assignee);
            }
        }
        if ($search = $request->input('search')) {
            $query->search($search);
        }
        if ($request->input('date_from') || $request->input('date_to')) {
            $query->byDateRange($request->input('date_from'), $request->input('date_to'));
        }
        if ($country = $request->input('country')) {
            $query->where('country', $country);
        }
        if ($erpStatus = $request->input('erp_sync_status')) {
            $query->where('erp_sync_status', $erpStatus);
        }
        if ($deviceType = $request->input('device_type')) {
            $query->where('device_type', $deviceType);
        }

        // Exclude deleted by default
        if (!$request->has('include_deleted')) {
            $query->where('status', '!=', 'deleted');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['created_at', 'updated_at', 'priority', 'status', 'name'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // Stats - counts by status (on unfiltered base)
        $statsQuery = Message::where('status', '!=', 'deleted');
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'new' => (clone $statsQuery)->where('status', 'new')->count(),
            'in_progress' => (clone $statsQuery)->whereIn('status', ['in_progress', 'waiting_info', 'waiting_customer', 'follow_up'])->count(),
            'quoted' => (clone $statsQuery)->whereIn('status', ['quoted', 'meeting_scheduled', 'demo_scheduled', 'qualified', 'negotiation'])->count(),
            'won' => (clone $statsQuery)->where('status', 'won')->count(),
            'lost' => (clone $statsQuery)->where('status', 'lost')->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', today())->count(),
        ];

        // Pagination
        $perPage = min((int) $request->input('per_page', 25), 100);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'messages' => $paginated->items(),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show single message with activities and notes
     */
    public function show(Request $request, string $id)
    {
        $msg = Message::with(['activities' => fn($q) => $q->latest()->limit(50), 'notes', 'assignee:id,name,email'])->find($id);
        if (!$msg) return response()->json(['error' => 'Talep bulunamadı'], 404);

        // Auto-mark as read
        $user = $request->user();
        if ($msg->status === 'new' && $user) {
            $this->messageService->updateStatus($msg, 'read', $user);
            $msg->refresh();
        }

        return response()->json(['message' => $msg]);
    }

    /**
     * Create new message (PUBLIC - contact form)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Capture device & location info
        $geoIp = app(GeoIpService::class);
        $ip = $request->ip();
        $ua = $request->userAgent() ?? '';
        $geo = $geoIp->lookup($ip);
        $device = $geoIp->parseUserAgent($ua);

        $msg = Message::create([
            'id' => 'msg-' . time() . '-' . mt_rand(1000, 9999),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'subject' => $request->service,
            'message' => $request->message,
            'status' => 'new',
            'priority' => 'normal',
            'source' => 'contact_form',
            // Device info
            'ip_address' => $ip,
            'user_agent' => mb_substr($ua, 0, 500),
            'device_type' => $device['device_type'] ?? null,
            'browser' => $device['browser'] ?? null,
            'browser_version' => $device['browser_version'] ?? null,
            'os' => $device['os'] ?? null,
            // Geo info
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'region' => $geo['region'] ?? null,
            'city' => $geo['city'] ?? null,
            'timezone' => $geo['timezone'] ?? null,
            'isp' => $geo['isp'] ?? null,
            // UTM/source
            'source_url' => $request->header('Referer'),
            'referrer_url' => $request->input('referrer_url'),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ]);

        // Notify admins
        $this->notificationService->notifyNewMessage($msg);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /**
     * Update message (status, priority, assignment, follow-up)
     */
    public function update(Request $request, string $id)
    {
        $msg = Message::find($id);
        if (!$msg) return response()->json(['error' => 'Talep bulunamadı'], 404);

        $user = $request->user();

        if ($request->has('status') && in_array($request->status, MessageService::STATUSES)) {
            $this->messageService->updateStatus($msg, $request->status, $user);
        }

        if ($request->has('priority') && in_array($request->priority, MessageService::PRIORITIES)) {
            $this->messageService->updatePriority($msg, $request->priority, $user);
        }

        if ($request->has('assigned_to')) {
            $this->messageService->assignTo($msg, $request->assigned_to ?: null, $user);
        }

        if ($request->has('follow_up_at')) {
            $this->messageService->setFollowUp($msg, $request->follow_up_at, $user);
        }

        $msg->refresh()->load(['assignee:id,name,email']);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /**
     * Delete message (soft - changes status to deleted)
     */
    public function destroy(Request $request, string $id)
    {
        $msg = Message::find($id);
        if (!$msg) return response()->json(['error' => 'Talep bulunamadı'], 404);

        $user = $request->user();

        // Super admin can hard delete
        if ($user->role === 'super_admin' && $request->input('permanent')) {
            $msg->delete();
            return response()->json(['success' => true, 'permanent' => true]);
        }

        $this->messageService->updateStatus($msg, 'deleted', $user);
        return response()->json(['success' => true]);
    }

    /**
     * Bulk actions on multiple messages
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'string',
            'action' => 'required|in:status,assign,delete',
            'value' => 'nullable|string',
        ]);

        $user = $request->user();
        $affected = 0;

        switch ($request->action) {
            case 'status':
                if (!in_array($request->value, MessageService::STATUSES)) {
                    return response()->json(['error' => 'Geçersiz durum'], 422);
                }
                $affected = $this->messageService->bulkUpdateStatus($request->ids, $request->value, $user);
                break;
            case 'assign':
                $affected = $this->messageService->bulkAssign($request->ids, (int) $request->value, $user);
                break;
            case 'delete':
                $affected = $this->messageService->bulkDelete($request->ids, $user);
                break;
        }

        return response()->json(['success' => true, 'affected' => $affected]);
    }

    /**
     * Sync message to ERP system
     */
    public function syncToErp(Request $request, string $id)
    {
        $msg = Message::find($id);
        if (!$msg) return response()->json(['error' => 'Talep bulunamadı'], 404);

        $erpService = app(ErpSyncService::class);
        $result = $erpService->syncMessageToCustomer($msg, $request->user());

        $status = $result['success'] ? 200 : 422;
        return response()->json($result, $status);
    }
}
