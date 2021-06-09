<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $table = 'coins';
    protected $fillable = [
        'name',
        'code',
        'icon',
        'type',
        'contract_address',
        'description',
        'decimal',
        'last_block_timestamp'
    ];

    const TYPE_TRC = 'trc';
    const TYPE_TRC10 = 'trc10';
    const TYPE_TRC20 = 'trc20';

    public function getTokenUrlAttribute(){
        $link = null;
        if($this->type == self::TYPE_TRC10){
            $link = 'https://tronscan.io/#/token/'.$this->contract_address;
        } else if($this->type == self::TYPE_TRC20){
            $link = 'https://tronscan.io/#/token20/'.$this->contract_address;
        }
        return $link;
    }
}
