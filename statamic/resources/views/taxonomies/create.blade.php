@extends('layout')

@section('content')

    <form method="post" action="{{ route('taxonomy.store') }}">

        <div class="publish-form card">
            <div class="head">
                <h1>{{ translate('cp.create_taxonomy') }}</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The name of your taxonomy.</small>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" />
                </div>

                <div class="form-group">
                    <label class="block">Slug</label>
                    <small class="help-block">This is how the taxonomy will be referenced in templates.</small>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" />
                </div>

                <div class="form-group">
                    <label class="block">Fieldset</label>
                    <small class="help-block">The fields that should be displayed when editing terms in this taxonomy.</small>
                    <fieldset-fieldtype name="fieldset" data="{{ old('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">Route</label>
                    <small class="help-block">The terms in this taxonomy will have URLs that follow this routing scheme.</small>
                    <input type="text" name="route" class="form-control" value="{{ old('route') }}" />
                </div>
            </div>

        </div>
    </form>

@endsection
