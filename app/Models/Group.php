<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;


class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'caption', 'image', 'room_id', 'profile_id'
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
        return $this->belongsToMany(Profile::class, 'profile_group')->withPivot('profile_id', 'state');
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
    public function getGroup($user_id, $state = true){
        $main_profile_id = User::find($user_id)->profiles()->whereIsMain(true)->first()->id;
        if($state)$this->toBase();
        $last_message = Room::find($this->room_id)->messages()->latest('created_at')->first();
        $room = [
            'id' => $this->id,
            'name' => $this->name,
            'caption' => $this->caption,
            'image' => $this->image,
            'state' => $this->profiles()->where('profiles.user_id', $user_id)->first()->pivot->state,
            'room_id' =>  $this->room_id,
            'profile_ids' => [
                $main_profile_id,
                $this->profiles()->where('profiles.user_id', $user_id)->first()->id
            ],
            'members' => [],
            'not_read' => User::find($user_id)->messages()->where('is_read', false)->whereRoomId($this->room_id)->count(),
            'last_updated_at' => $last_message ? $last_message->created_at : Room::find($this->room_id)->created_at,
            'last_message' => $last_message,
        ];
        $profiles = $this->profiles()->get();
        foreach ($profiles as $profile){
            $room["members"][$profile->user_id] = $profile->id;
        }

        return $room;
    }
}
