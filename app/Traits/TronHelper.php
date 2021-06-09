<?php

namespace App\Traits;

use App\Coin;
use GuzzleHttp\Client;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Tron;
use \IEXBase\TronAPI\Provider\HttpProvider;

trait TronHelper
{
    private function initTron($user = false, $privateKey = null)
    {
        $fullNode = new HttpProvider('https://api.trongrid.io');
        $solidityNode = new HttpProvider('https://api.trongrid.io');
        $eventServer = new HttpProvider('https://api.trongrid.io');
        $signServer = new HttpProvider('https://api.trongrid.io');
        $explorer = new HttpProvider('https://api.trongrid.io');

        try {
            if ($user) {
                $tron = new Tron($fullNode, $solidityNode, $eventServer, $signServer, $explorer, $privateKey);
            } else {
                $tron = new Tron($fullNode, $solidityNode, $eventServer);
            }
            return $tron;
        } catch (TronException $exception) {
            dd($exception);
        }
    }


    protected function checkTransaction($transaction_id, $from, $to, $total, $multipler, $contract, $coin_code)
    {
        $tron = $this->initTron();
        try {
            $check = $tron->getTransactionInfo($transaction_id);
            if ($check) {
                $response = $tron->getTransaction($transaction_id);
                if ($response['ret'][0]['contractRet'] == Transfer::SUCCESS_CODE) {
                    if($coin_code == 'TRX'){
                        $owner = $response['raw_data']['contract'][0]['parameter']['value']['owner_address'];
                        $destination = $response['raw_data']['contract'][0]['parameter']['value']['to_address'];
                        $amount = $response['raw_data']['contract'][0]['parameter']['value']['amount'];

                        if ($tron->toHex($from) != $owner) { // check owner
                            return [
                                'status' => false,
                                'message' => Transfer::WRONG_SENDER_CODE
                            ];
                        }

                        if ($tron->toHex($to) != $destination) { // check owner
                            return [
                                'status' => false,
                                'message' => Transfer::WRONG_RECEIVER_CODE
                            ];
                        }

                        if ($amount != $total * $multipler) {
                            return [
                                'status' => false,
                                'message' => Transfer::WRONG_AMOUNT_CODE
                            ];
                        }

                        return [
                            'status' => true,
                            'message' => Transfer::SUCCESS_CODE
                        ];
                    } else {
                        $owner = $response['raw_data']['contract'][0]['parameter']['value']['owner_address'];
                        $contract_address = $response['raw_data']['contract'][0]['parameter']['value']['contract_address'];
                        $data = $tron->getEventByTransactionID($transaction_id);
                        if ($data) {
                            $amount = $data[0]['result'][2];
                            if ($tron->toHex($from) != $owner) { // check owner
                                return [
                                    'status' => false,
                                    'message' => Transfer::WRONG_SENDER_CODE
                                ];
                            }

                            if ($tron->toHex($contract) != $contract_address) { // check contract
                                return [
                                    'status' => false,
                                    'message' => Transfer::WRONG_CONTRACT_CODE
                                ];
                            }

                            if ($amount != $total * $multipler) {
                                return [
                                    'status' => false,
                                    'message' => Transfer::WRONG_AMOUNT_CODE
                                ];
                            }

                            return [
                                'status' => true,
                                'message' => Transfer::SUCCESS_CODE
                            ];
                        } else {
                            return [
                                'status' => false,
                                'message' => Transfer::FAILED_CODE
                            ];
                        }
                    }
                } else {
                    return [
                        'status' => false,
                        'message' => $response['ret'][0]['contractRet']
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => Transfer::PENDING_CODE
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function sendTrx($to, $amount, $from, $privateKey, $is_foward = true)
    {
        $tron = $this->initTron(true,$privateKey);
        $message = null;
        $status = true;
        $txid = null;
        try {
            if($is_foward) {
                $balance = 0;
                $data = $tron->getAccount($from);
                if (!empty($data)) {
                    $balance = bcdiv($data['balance'], 1000000, 6);
                    $message = 'Success';
                    $transaction = $tron->sendTransaction($to, $balance, '', $from);
                    $txid = $transaction['txid'];
                } else {
                    $message = 'Balance 0';
                    $status = false;
                }
            } else {
                $message = 'Success';
                $transaction = $tron->sendTransaction($to, $amount, '', $from);
                $txid = $transaction['txid'];
            }
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }
        return [
            'status'=>$status,
            'data'=>[
                'message'=>$message,
                'txid'=>$txid
            ]
        ];
    }

    protected function sendTronToken($to, $amount, $from, $privateKey, $token_id)
    {
        $tron = $this->initTron(true,$privateKey);
        $message = null;
        $status = true;
        $txid = null;
        try {
            $balance = bcdiv($tron->getTokenBalance($token_id, $from), 1000000, 6);
            $message = 'Success';
            $transaction = $tron->sendTransaction($to, $balance, '', $from);
            $txid = $transaction['txid'];
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }
        return [
            'status'=>$status,
            'data'=>[
                'message'=>$message,
                'txid'=>$txid
            ]
        ];
    }

    protected function generateNewAddress(){
        $tron = $this->initTron();
        return $tron->generateAddress()->getRawData();
    }

    protected function getAllBalance($address){
        $result = [
            'status'=>true,
            'data'=>[]
        ];
        foreach (Coin::orderBy('id','desc')->get() as $coin){
            $result['data'][] = [
                'id'=>$coin->id,
                'name'=>$coin->code,
                'balance'=>$this->getBalance($address, $coin, $coin->type)
            ];
        }

        return $result;
    }

    protected function getBalance($address, $coin, $type){
        if($type == Coin::TYPE_TRC){
            return $this->getTRCBalance($address, $coin);
        } else if($type == Coin::TYPE_TRC10){
            return $this->getTRC10Balance($address, $coin);
        } else if($type == Coin::TYPE_TRC20) {
            return $this->getTRC20Balance($address, $coin);
        } else {
            return 0;
        }
    }

    protected function getTRCBalance($address, $coin){
        $tron = $this->initTron();
        $balance_uint = $tron->getBalance($address);
        $balance = bcdiv($balance_uint, $coin->multipler, $coin->decimal);

        return (string) $balance;
    }

    protected function getTRC10Balance($address, $coin){
        $tron = $this->initTron();
        $balance_uint = $tron->getTokenBalance($coin->contract_address, $address);
        $balance = bcdiv($balance_uint, $coin->multipler, $coin->decimal);
        return (string) $balance;
    }

    protected function getTRC20Balance($address, $coin){
//        $client = new Client();
//        $full_url = $coin->url_get_balance.$address;
//        $response = $client->get($full_url);
//        return (string) $response->getBody();
        return 0;
    }

}
