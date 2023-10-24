<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    use HasFactory;

    protected $primaryKey = ['provider_name', 'provider_user_id'];
    public $incrementing = false;

    protected $fillable = ['provider_name', 'provider_user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
