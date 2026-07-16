<?php
namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceTag;
use Illuminate\Http\Request;

class ServiceTagController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::where('is_active', 1)->orderBy('sort_order')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->title, 'category' => $s->category, 'source' => 'services']);
        $tags = ServiceTag::orderBy('name')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'category' => $t->category, 'source' => 'tags']);
        $all = collect($services)->merge($tags);
        if ($request->category) $all = $all->where('category', $request->category);
        return response()->json(['tags' => $all->values()]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $tag = ServiceTag::create(['id' => 'tag-' . time(), 'name' => trim($request->name), 'category' => $request->category]);
        return response()->json(['success' => true, 'tag' => $tag]);
    }
}
