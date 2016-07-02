@extends('outside')

@section('title')
    <h1>Page not found</h1>
    <hr>
@endsection

@section('content')

    <p>The page you requested does not exist.</p>

    <br>

    <div>
        <a class="btn btn-primary btn-block" href="{{ route('cp') }}">Dashboard</a>
    </div>

@endsection
