<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Room;

class BookingController extends Controller
{
    // ✅ Create booking
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'roomId' => 'required|integer|exists:rooms,id',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date|after:checkIn',
            'guests' => 'required|integer|min:1',
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'specialRequests' => 'nullable|string',
            'totalAmount' => 'required|numeric|min:0',
            'roomName' => 'nullable|string|max:255',
            'roomType' => 'nullable|string|max:100',
            'roomPrice' => 'nullable|numeric|min:0',
        ]);

        $booking = Booking::create([
            'user_id' => $validated['user_id'] ?? null,
            'room_id' => $validated['roomId'],
            'check_in' => $validated['checkIn'],
            'check_out' => $validated['checkOut'],
            'guests' => $validated['guests'],
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'special_requests' => $validated['specialRequests'] ?? null,
            'total_amount' => $validated['totalAmount'],
            'room_name' => $validated['roomName'] ?? null,
            'room_type' => $validated['roomType'] ?? null,
            'room_price' => $validated['roomPrice'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    // ✅ Fetch bookings for a specific user by email (no auth)
    public function userBookings(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['message' => 'Email is required'], 400);
        }

        $bookings = Booking::where('email', $email)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // ✅ Admin - fetch all bookings with full details
    public function allBookings()
    {
        $bookings = Booking::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'total' => $bookings->count(),
            'bookings' => $bookings
        ]);
    }

    // ✅ Admin - update booking status
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking = Booking::findOrFail($id); 
        $booking->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Booking status updated successfully',
            'booking' => $booking,
        ]);
    }
    
    public function getUserBookings($user_id)
    {
        try {
            // Fetch bookings with room info
            $bookings = Booking::with('room')
                ->where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json(['message' => 'No bookings found for this user.'], 200);
            }

            return response()->json($bookings, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ✅ Cancel booking (frontend button)
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 400);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }
    
}
