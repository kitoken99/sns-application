<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'account_type', 'name', 'caption', 'image', 'is_main'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'profile_room');
    }
    public function toBase()
    {
        $filePath = "public/profiles/" . $this->image;
        if (Storage::exists($filePath)) {
            $this->image = base64_encode(Storage::get($filePath));
        }
    }
}
