@extends('layout')

@section('content')

    @if (empty($widgets))

        <div class="card flat-bottom">
            <div class="head">
                <h1>{{ translate('cp.dashboard') }}</h1>
            </div>
        </div>
        <div class="card flat-top">
            <a href="{{ route('settings.edit', 'cp') }}" class="btn btn-primary">{{ translate('cp.configure')}}</a>
        </div>

    @else

        <div class="row">
            @foreach($widgets as $widget)
                <div class="{{ col_class($widget['width']) }}">
                    {!! $widget['html'] !!}
                </div>
            @endforeach
        </div>

    @endif

@stop
