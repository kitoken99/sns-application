<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermittedProfile extends Model
{

    protected $fillable = [
        'permition_id', 'profile_id'
    ];


    public function permition():BelongsTo
    {
        return $this->belongsTo(Permition::class);
    }
}
