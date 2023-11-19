<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Passport\HasApiTokens;
use App\Models\Provider;
use App\Models\Message;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'auth_type', 'exist', 'birthday'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'two_factor_confirmed_at',
        'current_team_id',
        'email_verified_at',
        'exist',
        'profile_photo_path',
        'profile_photo_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class);
    }
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'profile_group')->withPivot('profile_id', 'state');
    }

    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_user')->withPivot('is_read');
    }

    public static function socialFindOrCreate($providerUser, $provider)
    {
        $account = Provider::whereProviderName($provider)
                ->whereProviderUserId($providerUser->getId())
                ->first();


        // すでにアカウントがある場合は、そのユーザを返す
        if ($account) {
            return $account->user;
        }

        $existingUser = User::whereEmail($providerUser->getEmail())->first();

        if ($existingUser) {
            // メールアドレスはユニークの関係上、同一メールアドレスユーザがいる場合は、そのユーザと紐づけて認証プロバイダー情報登録
            $existingUser->update(['auth_type' =>'both']);
                $existingUser->Providers()->create([
                    'provider_user_id'   => $providerUser->getId(),
                    'provider_name' => $provider,
                ]);

                return $existingUser;

        } else {
            // アカウントがない場合は、ユーザ情報 + 認証プロバイダー情報を登録
                $providerUserName = $providerUser->getName() ? $providerUser->getName() : $providerUser->getNickname();
                $user = User::create([
                    'name'  => $providerUserName,
                    'auth_type' => 'social',
                    'email' => $providerUser->getEmail(),
                ]);
                $user->Profiles()->create([
                    'account_type' => 'main',
                    'name' => $user->name,
                    'is_main' => true,
                ]);
                $user->Providers()->create([
                    'provider_user_id'   => $providerUser->getId(),
                    'provider_name' => $provider,
                ]);
                return $user;
        }
    }
}
