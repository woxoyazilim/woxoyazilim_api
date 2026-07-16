<?php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index() { return response()->json(['messages' => Message::orderByDesc('created_at')->get()]); }

    public function show($id)
    {
        $msg = Message::find($id);
        if (!$msg) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['message' => $msg]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $msg = Message::create([
            'id' => 'msg-' . time() . '-' . mt_rand(1000, 9999),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'subject' => $request->service,
            'message' => $request->message,
            'status' => 'new',
        ]);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function update(Request $request, $id)
    {
        $msg = Message::find($id);
        if (!$msg) return response()->json(['error' => 'Not found'], 404);
        $msg->update($request->only(['status']));
        return response()->json(['success' => true]);
    }

    public function destroy($id) { Message::destroy($id); return response()->json(['success' => true]); }
}
