<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'caption', 'image', 'room_id'
    ];


    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'profile_group');
    }
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_group');
    }

    public function toBase(){
        $filePath = "public/group-images/" . $this->image;
        if (Storage::exists($filePath)) {
            $this->image = base64_encode(Storage::get($filePath));
        }
    }
    public function saveImage($image){
        $image->store('public/group-images');
        $file_name = $image->getClientOriginalName();
        $image->storeAs('public/group-images', $file_name);
        $this->fill(["image" => $file_name,]);
    }
    public function getRoom($user_id, $state = true){
        $main_profile_id = User::find($user_id)->profiles()->whereIsMain(true)->first()->id;
        if($state){
            if($this->image)$this->toBase();
            else $image = base64_encode(Storage::get( "public/profiles/user_default.image.png"));
        }else{
            if(!$this->image)$image = "user_default.image.png";
        }
        $room['room_id'] = $this->room_id;
        $room['profile_id'] = [];
        array_push($room['profile_id'],$main_profile_id);
        $room["name"] = $this->name?$this->name:"unknown";
        $room['caption'] = $this->caption?$this->caption:"";
        $room['image'] = $this->image?$this->image:$image;
        $room['members'] = [];
        $profiles = $this->profiles()->get();
        foreach ($profiles as $profile){
            $room["members"][$profile->user_id] = $profile->id;
            if($profile->user_id==$user_id){
                array_push($room['profile_id'],$profile->id);
            }
        }
        $room['not_read'] = User::find($user_id)->messages()->where('is_read', false)->whereRoomId($this->room_id)->count();
        $last_message = Room::find($this->room_id)->messages()->latest('created_at')->first();
        if($last_message){
            $last_message['name'] = Profile::whereUserId($last_message->user_id)->first()->name;
            $room['last_updated_at'] = $last_message->created_at;
        }else{
            $room['last_updated_at'] = Room::find($this->room_id)->created_at;
        }
        $room['last_message'] = $last_message;
        return $room;
    }
    public function setGroup($user_id, $state = true){
            if($state)$this->toBase();
            $members = [];
            $roomProfiles = $this->profiles()->get();
            foreach ($roomProfiles as $roomProfile){
                $members[$roomProfile->user_id] = $roomProfile->id;
            }
            $this->members = $members;
            $this->state = ProfileGroup::whereGroupId($this->id)->whereUserId($user_id)->first()->state;
    }
}
