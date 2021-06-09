<?php

use App\Coin;
use Illuminate\Database\Seeder;

class CoinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Coin::insert([
            [
                'name'=>'Tron',
                'code'=>'TRX',
                'icon'=>'/img/coin/trx.png',
                'type'=>Coin::TYPE_TRC,
                'contract_address'=>null,
                'description'=>'TRON is a Blockchain-based decentralized operating system based on a cryptocurrency native to the system, known as TRX.',
                'decimal'=>6
            ],
            [
                'name'=>'Indonesian Stable Coin',
                'code'=>'IDK',
                'icon'=>'/img/coin/idk.png',
                'type'=>Coin::TYPE_TRC20,
                'contract_address'=>'TYzEcJa2eaB4jG97BkLKoue9g1KBMxFxpL',
                'description'=>'IDK is Indonesian Rupiah as the stable coin in Tron Blockchain. 1 IDK equals with 1000 IDR.',
                'decimal'=>3
            ],
            [
                'name'=>'BitTorrent',
                'code'=>'BTT',
                'icon'=>'/img/coin/btt.png',
                'type'=>Coin::TYPE_TRC10,
                'contract_address'=>'1002000',
                'description'=>'Official Token of BitTorrent Protocol',
                'decimal'=>6
            ],
            [
                'name'=>'Kepeng Coin',
                'code'=>'KEPENG',
                'icon'=>'/img/coin/kepeng.png',
                'type'=>Coin::TYPE_TRC20,
                'contract_address'=>'TCC1h668oJB4H2iM9DFvw5aGMdFzZLJHo6',
                'description'=>'Kepeng Coin is a digital asset for agricultural commodities. We exchange agricultural products for digital assets using blockchain technology for transparency and security.',
                'decimal'=>8
            ],
            [
                'name'=>'Tether USD',
                'code'=>'USDT',
                'icon'=>'/img/coin/usdt.png',
                'type'=>Coin::TYPE_TRC20,
                'contract_address'=>'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'description'=>'USDT is the official stablecoin issued by Tether on the TRON network.',
                'decimal'=>6
            ],
        ]);
    }
}
