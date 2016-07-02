@extends('layout')

@section('content')

    <form method="post" action="{{ route('collection.update', $collection->path()) }}">

        <div class="publish-form card">
            <div class="head">

                <h1>
                    <i class="icon icon-cog"></i>
                    {{ $collection->title() }}
                </h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The name of your collection.</small>
                    <input type="text" name="fields[title]" class="form-control" value="{{ $collection->title() }}" />
                </div>

                <div class="form-group">
                    <label class="block">Fieldset</label>
                    <fieldset-fieldtype name="fields[fieldset]" data="{{ $collection->get('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">Route</label>
                    <small class="help-block">The entries in this collection will have URLs that follow this routing scheme.</small>
                    <input type="text" name="fields[route]" class="form-control" value="{{ $collection->route() }}" />
                </div>

            </div>
        </div>
    </form>

@endsection
