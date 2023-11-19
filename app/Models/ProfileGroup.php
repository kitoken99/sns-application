<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCompositePrimaryKeyTrait;

class ProfileGroup extends Model
{
    use HasFactory;
    use HasCompositePrimaryKeyTrait;
    protected $table = 'profile_group';
    protected $primaryKey = ['user_id', 'group_id'];
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'profile_id', 'group_id', 'state'
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

}
