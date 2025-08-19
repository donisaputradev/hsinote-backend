<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $notes = Note::where('user_id', $user->id)->latest()->get();

            return ResponseFormatter::success($notes, 'All notes data successfully retrieved.');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
            ]);

            $user = $request->user();

            $note = Note::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'user_id' => $user->id,
            ]);

            return ResponseFormatter::success($note, 'Create notes successfully.');
        } catch (\Illuminate\Validation\ValidationException $th) {
            return ResponseFormatter::error($th->errors(), $th->getMessage(), 422);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function update(Request $request, Note $note)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
            ]);

            $note->update($validated);

            return ResponseFormatter::success($note->refresh(), 'Update notes successfully.');
        } catch (\Illuminate\Validation\ValidationException $th) {
            return ResponseFormatter::error($th->errors(), $th->getMessage(), 422);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function delete(Note $note)
    {
        try {
            $note->delete();

            return ResponseFormatter::success(true, 'Delete notes successfully.');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }
}
