<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function wallets(){
        return $this->hasMany(Wallet::class);
    }

    public function hasWallet($coin_id){
        return !is_null(Wallet::where('user_id',$this->id)->where('coin_id',$coin_id)->first());
    }

    public function getAddressAttribute(){
        $wallet = Wallet::where('user_id',$this->id)->first();
        if(is_null($wallet)){
            $address = '';
            $private_key = '';
        } else {
            $address = $wallet->address;
            $private_key = $wallet->private_key;
        }
        return [
            'address'=>$address,
            'private_key'=>$private_key
        ];
    }
}
