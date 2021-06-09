<?php

namespace App\Http\Controllers;

use App\Coin;
use App\Traits\TronHelper;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    use TronHelper;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($code){
        $coin = Coin::whereCode($code)->first();
        if(is_null($coin)){
            abort(404);
        }
        $wallet = auth()->user()->wallets->where('coin_id',$coin->id)->first();
        if(is_null($wallet)){
            abort(404);
        }
        $transactions = $wallet->transactions->sortByDesc('id');
        return view('wallet',compact('coin','wallet','transactions'));
    }

    public function activate($code){
        $coin = Coin::whereCode($code)->first();
        if(is_null($coin)){
            abort(404);
        }
        if(!is_null(auth()->user()->wallets->where('coin_id',$coin->id)->first())){
            abort(404);
        }
        $tron_wallet = Wallet::where('user_id',auth()->user()->id)->first();
        $address = null;
        $private_key = null;
        if(is_null($tron_wallet)){
            $request = $this->generateNewAddress();
            $address = $request['address_base58'];
            $private_key = $request['private_key'];
        } else {
            $address = $tron_wallet->address;
            $private_key = $tron_wallet->private_key;
        }

        DB::transaction(function () use ($address, $private_key, $coin){
            Wallet::create([
                'user_id'=>auth()->user()->id,
                'coin_id'=>$coin->id,
                'address'=>$address,
                'private_key'=>$private_key,
                'balance'=>0,
            ]);
        });

        return redirect()->back()->with('message','Wallet '.$code.' created');
    }
}
