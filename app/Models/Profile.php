<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'account_type', 'name', 'caption', 'image', 'is_main', 'exist'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function friends(): HasMany
    {
        return $this->hasMany(Friend::class);
    }
    // public function rooms(): BelongsToMany
    // {
    //     return $this->belongsToMany(Room::class, 'profile_');
    // }
    public function toBase()
    {
        $filePath = "public/profiles/" . $this->image;
        if (Storage::exists($filePath)) {
            $this->image = base64_encode(Storage::get($filePath));
        }
    }
}
