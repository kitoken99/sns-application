<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'caption', 'image', 'room_id'
    ];


    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_group')->withPivot('user_id');
    }

    public function toBase()
    {
        $filePath = "public/group-images/" . $this->image;
        if (Storage::exists($filePath)) {
            $this->image = base64_encode(Storage::get($filePath));
        }
    }
}
