<?php

namespace App\Models;

use App\Events\Profile\ProfileUpdated;
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
        'user_id', 'account_type', 'name', 'caption', 'image', 'is_main', 'exist', 'show_birthday'
    ];
    protected $casts = [
        'is_main' => 'boolean',
        'show_birthday' => 'boolean',
    ];
    protected $hidden = [
        'exist',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $dispatchesEvents = [
        'updated' => ProfileUpdated::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function profileGroups(): HasMany
    {
        return $this->hasMany(ProfileGroup::class);
    }
    public function profileRooms(): HasMany
    {
        return $this->hasMany(RoomProfile::class);
    }
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'profile_group')->withPivot('profile_id', 'state');
    }
    public function permittion(): HasMany
    {
        return $this->hasMany(PermittedProfile::class);
    }
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class);
    }

    public function toBase()
    {
        $filePath = "public/profiles/" . $this->image;
        if (Storage::exists($filePath)) {
            $this->image = base64_encode(Storage::get($filePath));
        }
    }
    public function getBirthday(){
        if(User::find($this->user_id))
        $this->birthday = User::find($this->user_id)->birthday;
    }
    public function setProfile(){
        $this->toBase();
        $this->getBirthday();
    }
    public function saveImage($image){
        if(!$image){
            $this->image = "user_default.image.png";
            return;
        }
        $image->store('public/profiles');
        $file_name = $image->getClientOriginalName();
        $image->storeAs('public/profiles', $file_name);
        $this->image = $file_name;
    }
    public function getDefaultProfile(){
            $this->caption= "";
            $this->image= "user_default.image.png";
            $this->name= "unknown";
            $this->caption= "";
            $this->show_birthday= false;
    }
}
