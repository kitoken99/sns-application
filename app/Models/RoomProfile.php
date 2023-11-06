<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomProfile extends Model
{
    use HasFactory;
    protected $table = 'room_profile';

    protected $fillable = [
        'user_id', 'profile_id', 'room_id',
    ];
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
