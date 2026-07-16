<?php
namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function index()
    {
        $refs = Reference::orderBy('sort_order')->orderByDesc('created_at')->get();
        return response()->json(['references' => $refs]);
    }

    public function show($id)
    {
        $ref = Reference::find($id);
        if (!$ref) return response()->json(['error' => 'Reference not found'], 404);
        return response()->json(['reference' => $ref]);
    }

    public function store(Request $request)
    {
        $id = $request->id ?: 'ref-' . time() . rand(100, 999);

        $ref = Reference::create([
            'id' => $id,
            'title' => $request->title,
            'description' => $request->description,
            'image' => $request->image,
            'url' => $request->url,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'services' => $request->services ?: [],
            'year' => $request->year,
            'industry' => $request->industry,
            'type' => $request->type ?: 'web',
            'featured' => $request->featured ? 1 : 0,
            'sort_order' => $request->sortOrder ?: $request->sort_order ?: 0,
        ]);

        return response()->json(['success' => true, 'reference' => $ref]);
    }

    public function update(Request $request, $id)
    {
        $ref = Reference::find($id);
        if (!$ref) return response()->json(['error' => 'Not found'], 404);

        $ref->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $request->image,
            'url' => $request->url,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'services' => $request->services ?: [],
            'year' => $request->year,
            'industry' => $request->industry,
            'type' => $request->type ?: 'web',
            'featured' => $request->featured ? 1 : 0,
            'sort_order' => $request->sortOrder ?: $request->sort_order ?: 0,
        ]);

        return response()->json(['success' => true, 'reference' => $ref->fresh()]);
    }

    public function destroy($id)
    {
        $ref = Reference::find($id);
        if (!$ref) return response()->json(['error' => 'Not found'], 404);

        if ($ref->image && str_starts_with($ref->image, '/uploads/')) {
            @unlink(public_path($ref->image));
        }
        if ($ref->screenshot_url && str_starts_with($ref->screenshot_url, '/uploads/')) {
            @unlink(public_path($ref->screenshot_url));
        }

        $ref->delete();

        return response()->json(['success' => true]);
    }
}
