<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileGroup extends Model
{
    use HasFactory;
    protected $table = 'profile_group';
    protected $primaryKey = ['profile_id', 'group_id'];
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'profile_id', 'group_id', 'state'
    ];

}
