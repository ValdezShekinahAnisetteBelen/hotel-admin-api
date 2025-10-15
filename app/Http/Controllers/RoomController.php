<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        return Room::all();
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'amenities' => 'nullable|array',
        'amenities.*' => 'string',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $amenities = $validated['amenities'] ?? [];
    $imagePaths = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Save directly to /public/rooms
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('rooms'), $filename);

            // Relative path for DB/frontend
            $imagePaths[] = 'rooms/' . $filename;
        }
    }

    // ✅ Create room with all fields
    $room = Room::create([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'price' => $validated['price'],
        'amenities' => $amenities,
        'images' => $imagePaths, // use the correct array
        'status' => 'Available',
    ]);

    return response()->json($room, 201);
}

public function update(Request $request, Room $room)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'status' => 'required|in:Available,Occupied',
        'amenities' => 'nullable|array',
        'amenities.*' => 'string',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'existing_images' => 'nullable|array', // ✅ keep existing
        'existing_images.*' => 'string',
    ]);

    // ✅ Start with kept images
    $imagePaths = $validated['existing_images'] ?? [];

    // ✅ Add new uploaded images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('rooms'), $filename);
            $imagePaths[] = 'rooms/' . $filename;
        }
    }

    $room->update([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'price' => $validated['price'],
        'status' => $validated['status'],
        'amenities' => $validated['amenities'] ?? [],
        'images' => $imagePaths, // ✅ replaced with new + kept
    ]);

    return response()->json($room);
}

}
