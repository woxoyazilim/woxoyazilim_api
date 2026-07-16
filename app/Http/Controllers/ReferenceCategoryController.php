<?php
namespace App\Http\Controllers;

use App\Models\ReferenceCategory;
use Illuminate\Http\Request;

class ReferenceCategoryController extends Controller
{
    public function index()
    {
        return response()->json(['categories' => ReferenceCategory::where('is_active', 1)->orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $id = 'cat-' . time();
        $slug = $request->slug ?: \Illuminate\Support\Str::slug($request->name);
        $cat = ReferenceCategory::create(['id' => $id, 'name' => $request->name, 'slug' => $slug, 'icon' => $request->icon ?: 'Folder', 'sort_order' => $request->sort_order ?: 0]);
        return response()->json(['success' => true, 'category' => $cat]);
    }

    public function update(Request $request, $id)
    {
        $cat = ReferenceCategory::find($id);
        if (!$cat) return response()->json(['error' => 'Not found'], 404);
        $cat->update($request->only(['name', 'slug', 'icon', 'sort_order', 'is_active']));
        return response()->json(['success' => true, 'category' => $cat->fresh()]);
    }

    public function destroy($id)
    {
        ReferenceCategory::destroy($id);
        return response()->json(['success' => true]);
    }
}
