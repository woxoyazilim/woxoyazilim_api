<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageNote;
use App\Models\MessageActivity;
use Illuminate\Http\Request;

class MessageNoteController extends Controller
{
    public function index(string $messageId)
    {
        $notes = MessageNote::where('message_id', $messageId)->orderByDesc('is_pinned')->orderByDesc('created_at')->get();
        return response()->json(['notes' => $notes]);
    }

    public function store(Request $request, string $messageId)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'type' => 'nullable|in:note,call_log,follow_up,quote_note',
        ]);

        $msg = Message::find($messageId);
        if (!$msg) return response()->json(['error' => 'Talep bulunamadı'], 404);

        $user = $request->user();

        $note = MessageNote::create([
            'message_id' => $messageId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'content' => $request->content,
            'type' => $request->input('type', 'note'),
            'is_internal' => true,
        ]);

        // Log activity
        MessageActivity::create([
            'message_id' => $messageId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'note_added',
            'new_value' => $request->input('type', 'note'),
            'details' => mb_substr($request->content, 0, 100),
        ]);

        $msg->update(['last_action_at' => now()]);

        return response()->json(['success' => true, 'note' => $note], 201);
    }

    public function update(Request $request, string $messageId, int $noteId)
    {
        $note = MessageNote::where('message_id', $messageId)->find($noteId);
        if (!$note) return response()->json(['error' => 'Not bulunamadı'], 404);

        $user = $request->user();
        if ($note->user_id != $user->id && $user->role !== 'super_admin') {
            return response()->json(['error' => 'Bu notu düzenleme yetkiniz yok'], 403);
        }

        $request->validate(['content' => 'required|string|max:5000']);
        $note->update(['content' => $request->content]);

        return response()->json(['success' => true, 'note' => $note]);
    }

    public function destroy(Request $request, string $messageId, int $noteId)
    {
        $note = MessageNote::where('message_id', $messageId)->find($noteId);
        if (!$note) return response()->json(['error' => 'Not bulunamadı'], 404);

        $user = $request->user();
        if ($note->user_id != $user->id && $user->role !== 'super_admin') {
            return response()->json(['error' => 'Bu notu silme yetkiniz yok'], 403);
        }

        $note->delete();
        return response()->json(['success' => true]);
    }

    public function togglePin(Request $request, string $messageId, int $noteId)
    {
        $note = MessageNote::where('message_id', $messageId)->find($noteId);
        if (!$note) return response()->json(['error' => 'Not bulunamadı'], 404);

        $note->update(['is_pinned' => !$note->is_pinned]);
        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }
}
