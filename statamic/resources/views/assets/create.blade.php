@extends('layout')

@section('content')

    <form method="post" enctype="multipart/form-data" action="{{ route('asset.store') }}">

        <input type="hidden" name="container" value="{{ $container->uuid() }}" />
        <input type="hidden" name="folder" value="{{ $folder->path() }}" />

        <div class="publish-form card">
            <div class="head">
                <h1>Creating asset</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="publish-main col-md-9">

                    <div class="publish-fields">

                        <div class="form-group">
                            <label class="block">File</label>
                            <input type="file" name="file" />
                        </div>

                        <div class="form-group">
                            <label class="block">Title</label>
                            <small class="help-block">The display name of the asset.</small>
                            <input type="text" class="form-control" name="fields[title]" value="{{ old('fields.title') }}" />
                        </div>

                        <div class="form-group">
                            <label class="block">Alt Text</label>
                            <input type="text" class="form-control" name="fields[alt]" value="{{ old('fields.alt') }}" />
                        </div>

                    </div>

                </div>
                <div class="publish-meta col-md-3">

                    <div class="form-group">
                        <label class="block">Container</label>
                        <p class="form-control-static">{{ $container->title() }}</p>
                    </div>

                    <div class="form-group">
                        <label class="block">Path</label>
                        <p class="form-control-static">{{ $folder->path() }}</p>
                    </div>

                </div>
            </div>

    </form>

@endsection
