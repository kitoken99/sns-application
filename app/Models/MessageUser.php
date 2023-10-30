<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageUser extends Model
{
    protected $table = 'message_user';
    use HasFactory;
    protected $fillable = ['user_id', 'message_id', "is_read"];


}
