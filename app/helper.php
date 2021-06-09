<?php

if(!function_exists('getLastBlockTimestamp')){
    function getLastBlockTimeStamp($coin_code){
        $coin = \App\Coin::where('code', $coin_code)->first();
        return is_null($coin)?0:$coin->last_block_timestamp;
    }
}
