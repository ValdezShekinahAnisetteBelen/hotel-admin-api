<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bookings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'guests',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'special_requests',
        'room_name',
        'room_type',
        'room_price',
        'total_amount',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'room_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Each booking belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Each booking is for one room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Accessors & Helpers
     */

    // Automatically compute number of nights
    public function getNightsAttribute()
    {
        return $this->check_in && $this->check_out
            ? $this->check_in->diffInDays($this->check_out)
            : 0;
    }

    // Full guest name accessor
    public function getGuestNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Check if booking is active
    public function isActive()
    {
        return $this->status === 'confirmed' || $this->status === 'checked-in';
    }
}
