<?php

namespace App\Console\Commands;

use App\Coin;
use App\Traits\TronHelper;
use App\Transaction;
use App\User;
use App\Wallet;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanTRCToken extends Command
{
    use TronHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function handle()
    {
        $tron = $this->initTron();
        $addresses =  DB::table('wallets')
            ->select('address')
            ->groupBy('address')
            ->get()->pluck('address')->toArray();
        $available_coins =  DB::table('coins')
            ->select('code')
            ->whereIn('type',[Coin::TYPE_TRC, Coin::TYPE_TRC10])
            ->get()->pluck('code')->toArray();

        $parent_coin = Coin::where('code','TRX')->first();

        if(!is_null($parent_coin)) {
            foreach ($addresses as $address) {

                $url = 'https://api.trongrid.io/v1/accounts/' . $address . '/transactions?only_confirmed=true&limit=200&order_by=block_timestamp,asc';
                $client = new Client();
                $request = $client->get($url);
                $response = json_decode($request->getBody(), true);

                if ($response['meta']['page_size'] > 0 && $response['success'] == true) {
                    foreach ($response['data'] as $tx) {

                        $txType = $tx['raw_data']['contract'][0]['type'];
                        $listedType = ['TransferAssetContract', 'TransferContract'];
                        $param = $tx['raw_data']['contract'][0]['parameter']['value'];
                        $txid = $tx['txID'];
                        $block_timestamp = $tx['block_timestamp'];

                        if ($tx['ret'][0]['contractRet'] == 'SUCCESS' && in_array($txType, $listedType)) {
                            if ($tron->fromHex($param['to_address']) == $address) {
                                $from = $tron->fromHex($param['owner_address']);
                                if ($txType == $listedType[0]) {
                                    $token = $tron->getTokenByID($param['asset_name']);
                                    $code = $tron->fromHex($token['abbr']);
                                    if (in_array($code, $available_coins)) {
                                        $coin_symbol = $code;
                                        $decimal = $token['precision'];
                                        $multipler = 10 ** $decimal;
                                        $amount = $param['amount'] / $multipler;
                                        $coin = Coin::where('code', $coin_symbol)->first();
                                        $wallet = Wallet::where('address', $address)->where('coin_id', $coin->id)->first();
                                        if(!is_null($wallet)) {
                                            if($coin->last_block_timestamp >= $tx['block_timestamp']){
                                                continue;
                                            }
                                            DB::transaction(function () use ($coin_symbol, $address, $amount, $txid, $from, $wallet, $coin, $block_timestamp) {
                                                $save_tx = Transaction::create([
                                                    'user_id' => $wallet->user_id,
                                                    'wallet_id' => $wallet->id,
                                                    'coin_id' => $coin->id,
                                                    'type' => Transaction::TYPE_IN,
                                                    'category' => Transaction::CATEGORY_DEPOSIT,
                                                    'status' => Transaction::STATUS_SUCCESS,
                                                    'status_code' => Transaction::SUCCESS_CODE,
                                                    'txid' => $txid,
                                                    'from' => $from,
                                                    'to' => $address,
                                                    'amount' => $amount,
                                                    'fee' => 0,
                                                    'total_amount' => $amount,
                                                ]);
                                                $wallet->balance += $amount;
                                                $wallet->save();

                                                $coin->last_block_timestamp = $block_timestamp;
                                                $coin->save();
                                            });
                                            echo $coin_symbol .' recevied'. PHP_EOL;
                                        }
                                    }
                                } else {
                                    $coin_symbol = 'TRX';
                                    $decimal = 6;
                                    $multipler = 10 ** $decimal;
                                    $amount = $param['amount'] / $multipler;
                                    $coin = Coin::where('code', $coin_symbol)->first();
                                    $wallet = Wallet::where('address', $address)->where('coin_id', $coin->id)->first();
                                    if(!is_null($wallet)) {
                                        if($coin->last_block_timestamp >= $tx['block_timestamp']){
                                            continue;
                                        }
                                        DB::transaction(function () use ($coin_symbol, $address, $amount, $txid, $from, $wallet, $coin, $block_timestamp) {
                                            $save_tx = Transaction::create([
                                                'user_id' => $wallet->user_id,
                                                'wallet_id' => $wallet->id,
                                                'coin_id' => $coin->id,
                                                'type' => Transaction::TYPE_IN,
                                                'category' => Transaction::CATEGORY_DEPOSIT,
                                                'status' => Transaction::STATUS_SUCCESS,
                                                'status_code' => Transaction::SUCCESS_CODE,
                                                'txid' => $txid,
                                                'from' => $from,
                                                'to' => $address,
                                                'amount' => $amount,
                                                'fee' => 0,
                                                'total_amount' => $amount,
                                            ]);
                                            $wallet->balance += $amount;
                                            $wallet->save();

                                            $coin->last_block_timestamp = $block_timestamp;
                                            $coin->save();
                                        });
                                        echo $coin_symbol .' recevied'. PHP_EOL;
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
    }
}
