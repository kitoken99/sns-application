<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'profile_id', 'friend_user_id','friend_profile_id','room_id', 'state','is_top'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
