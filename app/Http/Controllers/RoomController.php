<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Booking;

class RoomController extends Controller
{
public function bookingReports(Request $request)
    {
        $query = Booking::with(['user', 'room']);

        // Optional date filter (check_in / check_out / created_at)
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        // Optional status filter (confirmed, cancelled, etc.)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json($bookings);
    }


    public function index()
    {
        return Room::all();
    }

    public function featured()
        {
            $featuredRooms = Room::where('featured', true)
                ->where('status', 'Available')
                ->get()
                ->map(function ($room) {
                    // Convert image paths to full URLs
                    $room->images = collect($room->images)->map(fn($path) => asset($path));
                    return $room;
                });

            return response()->json($featuredRooms);
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
            'featured' => 'nullable|boolean',
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
            'amenities' => $validated['amenities'] ?? [],
            'images' => $imagePaths,
            'status' => 'Available',
            'featured' => $validated['featured'] ?? false,
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

    public function discounted()
{
    $discountedRooms = Room::where('discount', '>', 0)
        ->where('status', 'Available')
        ->get()
        ->map(function ($room) {
            // Convert image paths to full URLs
            $room->images = collect($room->images)->map(fn($path) => asset($path));
            return $room;
        });

    return response()->json($discountedRooms);
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
        'featured' => 'nullable|boolean',
        'discount' => 'nullable|numeric|min:0|max:100', // ✅ add this
    ]);

    // Keep existing images
    $imagePaths = $validated['existing_images'] ?? [];

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
        'featured' => $validated['featured'] ?? false,
        'discount' => $validated['discount'] ?? 0, // ✅ apply
    ]);

    return response()->json($room);
}

}