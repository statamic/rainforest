@extends('layout')

@section('content')

    <form method="post" action="{{ route('collection.store') }}">

        <div class="card flat-bottom sticky">
            <div class="head">
                <h1>{{ translate('cp.create_collection') }}</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>
        </div>
        <div class="publish-form card flat-top">

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The name of your collection.</small>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" />
                </div>

                <div class="form-group">
                    <label class="block">Slug</label>
                    <small class="help-block">This is how the group will be referenced in templates.</small>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}"/>
                </div>

                <div class="form-group">
                    <label class="block">Order</label>
                    <small class="help-block">How should the entries in this collection be ordered?</small>
                    <select-fieldtype name="order" data="{{ old('order') }}" :options='[
                        {"value": "", "text": "Alphabetical"},
                        {"value": "date", "text": "Date"},
                        {"value": "number", "text": "Number"}
                    ]'></select-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">Fieldset</label>
                    <fieldset-fieldtype name="fieldset" data="{{ old('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">Route</label>
                    <small class="help-block">The entries in this collection will have URLs that follow this routing scheme.</small>
                    <input type="text" name="route" class="form-control" value="{{ old('route') }}" />
                </div>

            </div>
        </div>
    </form>

@endsection
