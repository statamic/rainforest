@extends('layout')

@section('content')

    <form method="post" action="{{ route('globals.update', $global->slug()) }}">

        <div class="publish-form card">
            <div class="head">

                <h1>
                    <i class="icon icon-cog"></i>
                    {{ $global->title() }}
                </h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The name of your global set.</small>
                    <input type="text" name="title" class="form-control" value="{{ $global->title() }}" />
                </div>

                <div class="form-group">
                    <label class="block">Fieldset</label>
                    <fieldset-fieldtype name="fieldset" data="{{ $global->fieldset()->name() }}"></fieldset-fieldtype>
                </div>

            </div>
        </div>
    </form>

@endsection
