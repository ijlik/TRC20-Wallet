<?php

namespace App\Console\Commands;

use App\Coin;
use App\Traits\TronHelper;
use App\Transaction;
use App\Wallet;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanTRC20Token extends Command
{
    use TronHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:trc20';

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
     */
    public function handle()
    {
        $tron = $this->initTron();

        $addresses =  DB::table('wallets')
            ->select('address')
            ->groupBy('address')
            ->get()->pluck('address')->toArray();

        $coins = Coin::where('type',Coin::TYPE_TRC20)->get();
        foreach ($coins as $coin) {
            $contract = $coin->contract_address;
            $min_block_timestamp = $coin->last_block_timestamp;
            if($min_block_timestamp == 0) {
                $url = 'https://api.trongrid.io/v1/contracts/' . $contract . '/events?limit=200&only_confirmed=true&event_name=Transfer&order_by=block_timestamp,desc';
            } else {
                $url = 'https://api.trongrid.io/v1/contracts/' . $contract . '/events?limit=200&only_confirmed=true&event_name=Transfer&order_by=block_timestamp,desc&min_block_timestamp='.$min_block_timestamp;
            }
            $client = new Client();
            $request = $client->get($url);
            $response = json_decode($request->getBody(), true);

            if ($response['meta']['page_size'] > 0 && $response['success'] == true) {
                foreach ($response['data'] as $tx){
                    if($coin->last_block_timestamp >= $tx['block_timestamp']){
                        continue;
                    }
                    $from = $tron->fromHex("41".substr($tx['result'][0],2,40));
                    $to = $tron->fromHex("41".substr($tx['result'][1],2,40));
                    $txid = $tx['transaction_id'];
                    $block_timestamp = $tx['block_timestamp'];
                    if(in_array($to, $addresses)) {
                        $wallet = Wallet::where('coin_id',$coin->id)->where('address',$to)->first();
                        if(!is_null($wallet)) {
                            $decimal = $tron->contract($contract)->decimals();
                            $multipler = 10 ** $decimal;
                            $amount = $tx['result'][2] / $multipler;
                            DB::transaction(function () use ($from, $to, $wallet, $amount, $coin, $txid, $block_timestamp){
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
                                    'to' => $to,
                                    'amount' => $amount,
                                    'fee' => 0,
                                    'total_amount' => $amount,
                                ]);
                                $wallet->balance += $amount;
                                $wallet->save();

                                $coin->last_block_timestamp = $block_timestamp;
                                $coin->save();
                            });
                            echo $txid.' => '.$to.' | '.$amount.PHP_EOL;
                        }
                    }
                }
            }
        }
    }
}
