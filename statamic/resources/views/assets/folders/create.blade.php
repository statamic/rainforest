@extends('layout')

@section('content')

    <form method="post" action="{{ route('assets.folder.store') }}">

        <input type="hidden" name="container" value="{{ $container->uuid() }}" />
        <input type="hidden" name="parent" value="{{ $parent }}" />

        <div class="publish-form card">
            <div class="head clearfix">
                <h1>Creating a new folder</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">Title</label>
                    <small class="help-block">The display name of the container.</small>
                    <input type="text" class="form-control" name="title" />
                </div>

                <div class="form-group">
                    <label class="block">Name</label>
                    <small class="help-block">The filesystem directory name</small>
                    <input type="text" class="form-control" name="basename" />
                </div>

            </div>
        </div>

    </form>

@endsection
