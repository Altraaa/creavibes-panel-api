<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    protected $fillable = [
        'name',
        'age',
        'address',
        'current_activity',
        'university',
        'has_event_experience',
        'event_experience_details',
        'user_id',
    ];

    protected $casts = [
        'age' => 'integer',
        'has_event_experience' => 'boolean',
    ];

    /**
     * Get the user that owns the volunteer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
