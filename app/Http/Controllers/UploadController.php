<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function referenceImage(Request $request)
    {
        if (!$request->hasFile('file')) return response()->json(['error' => 'Dosya gerekli'], 400);

        $file = $request->file('file');
        $filename = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move(public_path('uploads/references'), $filename);

        return response()->json([
            'success' => true,
            'imageUrl' => '/uploads/references/' . $filename,
            'fileName' => $filename,
        ]);
    }
}
