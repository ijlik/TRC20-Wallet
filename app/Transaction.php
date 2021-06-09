<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'wallet_id',
        'coin_id',
        'type',
        'category',
        'status',
        'status_code',
        'txid',
        'from',
        'to',
        'amount',
        'fee',
        'total_amount',
        'notes',
    ];

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';

    const CATEGORY_DEPOSIT = 'deposit';
    const CATEGORY_WITHDRAW = 'withdraw';
    const CATEGORY_SEND = 'send';
    const CATEGORY_RECEIVE = 'receive';

    const STATUS_PENDING = 'pending';
    const STATUS_HOLD = 'hold';
    const STATUS_SUCCESS = 'success';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    const PENDING_CODE = 'PENDING';
    const SUCCESS_CODE = 'SUCCESS';
    const FAILED_CODE = 'FAILED';
    const WRONG_SENDER_CODE = 'WRONG SENDER';
    const WRONG_CONTRACT_CODE = 'WRONG CONTRACT';
    const WRONG_RECEIVER_CODE = 'WRONG RECEIVER';
    const WRONG_AMOUNT_CODE = 'WRONG AMOUNT';
    const WAITING_CONFIRMATION_CODE = 'WAITING FOR CONFIRMATION';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function wallet(){
        return $this->belongsTo(Wallet::class);
    }

    public function coin(){
        return $this->belongsTo(Coin::class);
    }

    public function getMultiplerAttribute(){
        $decimal = $this->coin->decimal ?? 0;
        return 10 ** $decimal;
    }

    public function getTxidLinkAttribute(){
        return 'https://tronscan.org/#/transaction/'.$this->txid;
    }
}
