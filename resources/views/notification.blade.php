@if (! empty(session('message')))
    @if (is_array(session('message')))
        <script>swal("{!! session('message.content') !!}", "{!! session('message.title') !!}",'success');</script>
    @else
        <script>swal("{!! session('message') !!}",'Messages','success');</script>
    @endif
@endif

@if (session()->has('status'))
    <script>swal("{!! session()->get('status') !!}",'Success','success');</script>
@endif

@if (count($errors) > 0)
    @php
        $pesan = '';
        foreach ($errors->all() as $error){
            $pesan.=$error.', ';
        }
    @endphp
    <script>swal('{{ $pesan }}','','info')</script>
@endif
