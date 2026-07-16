<?php
namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Subcategory::orderBy('name');
        if ($request->sector_id) $q->where('sector_id', $request->sector_id);
        return response()->json(['subcategories' => $q->get()]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $sub = Subcategory::create(['id' => 'subcat-' . time(), 'name' => trim($request->name), 'sector_id' => $request->sector_id]);
        return response()->json(['success' => true, 'subcategory' => $sub]);
    }
}
