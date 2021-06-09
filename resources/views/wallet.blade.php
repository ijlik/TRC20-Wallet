@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @include('layouts.menu')
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><img src="{{ $coin->icon }}" style="height: 20px; width: auto"> {{ $coin->code }} Wallet
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h3>{{ number_format($wallet->balance,$coin->decimal) }} <small>{{ $coin->code }}</small></h3>
                                <hr>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <a href="#"><span class="input-group-text">Copy Address</span></a>
                                    </div>
                                    <input type="text" value="{{ $wallet->address }}" readonly="readonly" class="form-control">
                                </div>
                                <h5>Scan QR Code below to facilitate deposit via mobile.</h5>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $wallet->address }}">
                                <ul class="text-left">
                                    <li>The above address is only for you, and can be used repeatedly</li>
                                    <li>Deposit will be added to your wallet after 20 confirmation (10-20 minutes)</li>
                                    <li>Deposit {{ $wallet->coin->code }} is free of charge</li>
                                    <li>Sending token other than {{ $wallet->coin->code }} to above address will cause the token to lost.</li>
                                </ul>
                                <hr>
                                <table class="table">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Txid</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <th scope="row">{{ $transaction->created_at }}</th>
                                            <td>{{ $transaction->category }}</td>
                                            <td>{{ number_format($transaction->total_amount,$coin->decimal) }}</td>
                                            <td><a href="{{ $transaction->txid_link }}" target="_blank">tronscan txid</a></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
