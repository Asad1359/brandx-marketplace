<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bag;
use Illuminate\Http\Request;

class BagController extends Controller
{
    /**
     * Display all bags.
     */
    public function index()
    {
        $bags = Bag::latest()->get();

        return response()->json([
            'success' => true,
            'bags' => $bags,
        ]);
    }

    /**
     * Display a single bag.
     */
    public function show($id)
    {
        $bag = Bag::find($id);

        if (!$bag) {
            return response()->json([
                'success' => false,
                'message' => 'Bag not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'bag' => $bag,
        ]);
    }

    /**
     * Store a new bag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'color' => [
                'nullable',
                'string',
                'max:100',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'image' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ]);

        $bag = Bag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bag created successfully.',
            'bag' => $bag,
        ], 201);
    }

    /**
     * Update a bag.
     */
    public function update(Request $request, $id)
    {
        $bag = Bag::find($id);

        if (!$bag) {
            return response()->json([
                'success' => false,
                'message' => 'Bag not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'color' => [
                'nullable',
                'string',
                'max:100',
            ],

            'quantity' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'image' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ]);

        $bag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bag updated successfully.',
            'bag' => $bag->fresh(),
        ]);
    }

    /**
     * Delete a bag.
     */
    public function destroy($id)
    {
        $bag = Bag::find($id);

        if (!$bag) {
            return response()->json([
                'success' => false,
                'message' => 'Bag not found.',
            ], 404);
        }

        $bag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bag deleted successfully.',
        ]);
    }
}