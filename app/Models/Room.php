<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

}
