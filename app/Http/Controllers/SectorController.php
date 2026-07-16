<?php
namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index() { return response()->json(['sectors' => Sector::orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $sector = Sector::create(['id' => 'sector-' . time(), 'name' => trim($request->name)]);
        return response()->json(['success' => true, 'sector' => $sector]);
    }
}
