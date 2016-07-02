@extends('layout')

@section('content')

    <form method="post" action="{{ route('assets.folder.update', [$container->uuid(), $folder->path()]) }}">

        <div class="publish-form card">
            <div class="head">
                <h1>Editing asset folder</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The display name of the folder.</small>
                    <input type="text" class="form-control" name="title" value="{{ $folder->title() }}" />
                </div>

            </div>
        </div>

    </form>

@endsection
