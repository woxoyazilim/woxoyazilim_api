<?php
namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageContent;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index() { return response()->json(['pages' => Page::all()]); }

    public function show($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if (!$page) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['page' => $page]);
    }

    public function content($slug)
    {
        $pc = PageContent::find($slug);
        return response()->json(['content' => $pc?->content, 'slug' => $slug]);
    }

    public function updateContent(Request $request, $slug)
    {
        PageContent::updateOrCreate(['slug' => $slug], ['content' => $request->content]);
        return response()->json(['success' => true]);
    }
}
