<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;
    protected $primaryKey = ['profile_id', 'room_id'];
    public $incrementing = false;

    protected $fillable = [
        'profile_id', 'room_id'
    ];

    public function Profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

}
