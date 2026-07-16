<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(['services' => Service::orderBy('sort_order')->orderByDesc('created_at')->get()]);
    }

    public function show($id)
    {
        $svc = Service::find($id);
        if (!$svc) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['service' => $svc]);
    }

    public function store(Request $request)
    {
        $svc = Service::create(array_merge($request->all(), ['id' => $request->id ?: 'svc-' . time()]));
        return response()->json(['success' => true, 'service' => $svc]);
    }

    public function update(Request $request, $id)
    {
        $svc = Service::find($id);
        if (!$svc) return response()->json(['error' => 'Not found'], 404);
        $svc->update($request->all());
        return response()->json(['success' => true, 'service' => $svc->fresh()]);
    }

    public function destroy($id)
    {
        Service::destroy($id);
        return response()->json(['success' => true]);
    }
}
