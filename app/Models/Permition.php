<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permition extends Model
{


    public function permittedProfiles():HasMany
    {
        return $this->hasMany(PermittedProfile::class);
    }
}
