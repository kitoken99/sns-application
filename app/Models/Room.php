<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
    ];

    // public function profiles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Profile::class, 'profile_room');
    // }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }







    public function profiles()
    {
        $room_profiles =  $this->hasMany(RoomProfile::class)->get();
        $response = [];
        foreach($room_profiles as $room_profile){
            array_push($response, Profile::find($room_profile->profile_id));
        };
        return $response;
    }

}
