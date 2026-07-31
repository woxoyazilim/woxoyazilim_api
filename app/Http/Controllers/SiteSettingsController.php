<?php
namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'id');
        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['id' => $key],
                ['value' => $value]
            );
        }

        $settings = SiteSetting::all()->pluck('value', 'id');
        return response()->json(['success' => true, 'settings' => $settings]);
    }
}
