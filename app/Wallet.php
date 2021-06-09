<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $fillable = [
        'user_id',
        'coin_id',
        'address',
        'private_key',
        'balance',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function coin(){
        return $this->belongsTo(Coin::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
