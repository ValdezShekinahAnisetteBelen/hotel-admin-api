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
                // Save to public/rooms directory
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rooms'), $filename);
                
                // Store relative path
                $imagePaths[] = 'rooms/' . $filename;
            }
        }

        $room = Room::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'amenities' => $amenities,
            'images' => $imagePaths,
            'status' => 'Available',
        ]);

        return response()->json($room, 201);
    }

     public function show(Room $room)
    {
        return response()->json($room);
    }

       public function destroy(Room $room)
    {
        // Optionally delete images from storage
        if (!empty($room->images)) {
            foreach ($room->images as $imagePath) {
                $fullPath = public_path($imagePath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted successfully']);
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
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
        ]);

        // Start with existing images
        $imagePaths = $validated['existing_images'] ?? [];

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
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
            'images' => $imagePaths,
        ]);

        return response()->json($room);
    }
}