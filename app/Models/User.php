<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_available',
        'last_active_at',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'profile_image',
        'theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'last_active_at' => 'datetime',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function chatConversation()
    {
        return $this->hasOne(ChatConversation::class);
    }

    public function assignedConversations()
    {
        return $this->hasMany(ChatConversation::class, 'assigned_to');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function chatRatings()
    {
        return $this->hasMany(ChatRating::class);
    }
}