<?php
namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Services\LeadMailService;
use Illuminate\Http\Request;

class DemoRequestController extends Controller
{
    public function __construct(
        protected LeadMailService $leadMailService,
    ) {}

    public function index()
    {
        return response()->json([
            'demoRequests' => DemoRequest::orderByDesc('created_at')->get()
        ]);
    }

    public function store(Request $request)
    {
        $dr = DemoRequest::create([
            'id' => 'demo-' . time() . rand(100, 999),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'software_name' => $request->software ?? $request->softwareName ?? null,
            'software_slug' => $request->softwareSlug ?? $request->service_id ?? null,
            'employee_count' => $request->employeeCount ?? $request->employee_count ?? null,
            'service_id' => $request->softwareSlug ?? $request->service_id ?? null,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // E-posta bildirimi (hata olsa bile talep kaydı korunur)
        $this->leadMailService->sendDemoRequest($dr);

        return response()->json(['success' => true, 'demoRequest' => $dr]);
    }

    public function update(Request $request, $id)
    {
        $dr = DemoRequest::find($id);
        if (!$dr) return response()->json(['error' => 'Not found'], 404);
        $dr->update($request->only(['status']));
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DemoRequest::destroy($id);
        return response()->json(['success' => true]);
    }
}
