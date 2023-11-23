<?php

namespace App\Models;

use App\Events\Friendship\FriendshipCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

class Friendship extends Model
{
    use HasFactory;
    protected $table = 'friends';
    protected $fillable = [
        'user_id','friend_user_id', 'permitting_id', 'permitted_id', 'profile_id', 'room_id', 'state'
    ];
    protected $dispatchesEvents = [
        'created' => FriendshipCreated::class,
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    public function permittedProfiles(){
        $permitted_profiles = Permition::find($this->permitted_id)->hasMany(PermittedProfile::class)->get();
        $profiles = [];
        foreach($permitted_profiles as $permitted_profile){
            array_push($profiles, Profile::find($permitted_profile->profile_id));
        }
        return $profiles;
    }
    public function permittingProfiles(){
        $permitted_profiles = Permition::find($this->permitting_id)->hasMany(PermittedProfile::class)->get();
        $profiles = [];
        foreach($permitted_profiles as $permitted_profile){
            array_push($profiles, Profile::find($permitted_profile->profile_id));
        }
        return $profiles;
    }
    public function getFriendship(){
        $friendship["user_id"] = $this->friend_user_id;
        $friendship["profile_id"] = $this->profile_id;
        $friendship["room_id"] = $this->room_id;
        $friendship["state"] = $this->state;
        if(!User::find($this->friend_user_id)){
          $friendship["state"] = "deleted";
        }
        $friendship['profile_ids'] = [];
        $permitting_profiles = $this->permittingProfiles();
        foreach($permitting_profiles as $permitting_profile){
            array_push($friendship['profile_ids'], $permitting_profile->id);
        }
        $friendship['not_read'] = User::find($this->user_id)->messages()->where('is_read', false)->whereRoomId($this->room_id)->count();
        $last_message = Room::find($this->room_id)->messages()->latest('created_at')->first();
        if($last_message){
            $friendship['last_updated_at'] = $last_message->created_at;
        }else{
            $friendship['last_updated_at'] = Room::find($this->room_id)->created_at;
        }
        $friendship['last_message'] = $last_message;
        return $friendship;
    }

    public function getRoom($state = true){
        $profile = Profile::find($this->profile_id);
        if($state){
            if($profile)$profile->toBase();
            else $image = base64_encode(Storage::get( "public/profiles/user_default.image.png"));
        }else{
            if(!$profile)$image = "user_default.image.png";
        }
        $room['room_id'] = $this->room_id;
        $room['profile_id'] = [];
        $permitting_profiles = $this->permittingProfiles();
        foreach($permitting_profiles as $permitting_profile){
            array_push($room['profile_id'], $permitting_profile->id);
        }
        $room["name"] = $profile?$profile->name:"unknown";
        $room['caption'] = $profile?$profile->caption:"";
        $room['image'] = $profile?$profile->image:$image;
        $room["members"][$this->user_id] = null;
        $room["members"][$this->friend_user_id] = $this->profile_id;
        $room['not_read'] = User::find($this->user_id)->messages()->where('is_read', false)->whereRoomId($this->room_id)->count();
        $last_message = Room::find($this->room_id)->messages()->latest('created_at')->first();
        if($last_message){
            $last_message_profile = Profile::whereUserId($last_message->user_id)->first();
            $last_message['name'] = $last_message_profile?$last_message_profile->name:"unknown";
            $room['last_updated_at'] = $last_message->created_at;
        }else{
            $room['last_updated_at'] = Room::find($this->room_id)->created_at;
        }
        $room['last_message'] = $last_message;
        return $room;
    }
}
