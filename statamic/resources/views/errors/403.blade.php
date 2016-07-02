@extends('outside')

@section('title')
    <h1>Permission Denied</h1>
    <hr>
@endsection

@section('content')

    <p>You don't have permission to view this page.</p>

    <br>

    <div>
        @if (Auth::check())
            <a class="btn btn-primary btn-block" href="{{ route('logout') }}">Log out</a>
        @else
            <a class="btn btn-primary btn-block" href="{{ route('login') }}">Log in</a>
        @endif
    </div>

@endsection
