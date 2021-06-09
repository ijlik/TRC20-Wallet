@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @include('layouts.menu')
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <div class="row">
                        @foreach($coins as $coin)
                        <div class="col-md-4" style="padding-bottom: 30px;">
                            <div class="card" style="border: 1px #d7d7d7 solid;">
                                <div class="card-header"><img src="{{ $coin->icon }}" style="height: 20px; width: auto"> {{ $coin->code }}</div>
                                <div class="card-body text-center">
                                    @if(auth()->user()->hasWallet($coin->id))
                                        <h3>{{ number_format(auth()->user()->wallets->where('coin_id',$coin->id)->first()->balance,$coin->decimal) }}</h3>
                                        <a href="/wallet/{{ $coin->code }}" class="btn btn-sm btn-primary">Wallet</a>
                                    @else
                                        <h3>0</h3>
                                        <a href="/wallet/{{ $coin->code }}/activate" class="btn btn-sm btn-success">Activate</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
