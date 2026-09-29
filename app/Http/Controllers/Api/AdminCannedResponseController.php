<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use Illuminate\Http\Request;

class AdminCannedResponseController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'responses' => CannedResponse::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:5000',
            'shortcut' => 'nullable|string|max:50',
        ]);

        $response = CannedResponse::create($validated);

        return response()->json([
            'success' => true,
            'response' => $response,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $canned = CannedResponse::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:5000',
            'shortcut' => 'nullable|string|max:50',
        ]);

        $canned->update($validated);

        return response()->json([
            'success' => true,
            'response' => $canned,
        ]);
    }

    public function destroy($id)
    {
        CannedResponse::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}