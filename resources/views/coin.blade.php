@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @include('layouts.menu')
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Coin List</div>

                    <div class="card-body">
                        <div class="row">
                            <table class="table">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Icon</th>
                                    <th scope="col">Decimal</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($coins as $coin)
                                <tr>
                                    <th scope="row">{{ $coin->id }}</th>
                                    <td>
                                        <details>
                                            <summary>{{ $coin->name }}</summary>
                                            <p>{{ $coin->description }}</p>
                                        </details>
                                    </td>
                                    <td>{{ $coin->code }}</td>
                                    <td>
                                        <img src="{{ $coin->icon }}" style="height: 20px; width: auto">
                                        @if(!is_null($coin->token_url))
                                            <a target="_blank" href="{{ $coin->token_url }}">Token Url</a>
                                        @endif
                                    </td>
                                    <td>{{ $coin->decimal }}</td>
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
@endsection
