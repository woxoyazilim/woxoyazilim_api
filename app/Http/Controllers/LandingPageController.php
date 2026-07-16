<?php
namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->has('active') ? LandingPage::where('is_active', 1) : LandingPage::query();
        return response()->json(['pages' => $q->orderBy('region')->orderBy('sort_order')->get()]);
    }

    public function show($id)
    {
        $page = LandingPage::where('id', $id)->orWhere('slug', $id)->first();
        if (!$page) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['landingPage' => $page]);
    }

    public function store(Request $request)
    {
        $page = LandingPage::create(array_merge($request->all(), ['id' => 'lp-' . time()]));
        return response()->json(['success' => true, 'landingPage' => $page]);
    }

    public function update(Request $request, $id)
    {
        $page = LandingPage::find($id);
        if (!$page) return response()->json(['error' => 'Not found'], 404);
        $page->update($request->all());
        return response()->json(['success' => true, 'landingPage' => $page->fresh()]);
    }

    public function destroy($id) { LandingPage::destroy($id); return response()->json(['success' => true]); }
}
