<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['provider_name', 'provider_user_id', "exist"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
