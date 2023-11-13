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
        'user_id','friend_user_id', 'permitting_id', 'permitted_id', 'profile_id', 'room_id', 'state'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

}
