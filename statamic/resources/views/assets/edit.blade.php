@extends('layout')

@section('content')

    <form method="post" action="{{ route('asset.update', $asset->getUuid()) }}">

        <div class="publish-form card">
            <div class="head clearfix">
                <h1>Editing asset</h1>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="publish-main col-md-9">

                    <div class="publish-fields">

                        <div class="form-group">
                            <label class="block">Title</label>
                            <small class="help-block">The display name of the asset.</small>
                            <input type="text" class="form-control" name="fields[title]" value="{{ $asset->get('title') }}" />
                        </div>

                        <div class="form-group">
                            <label class="block">Alt Text</label>
                            <input type="text" class="form-control" name="fields[alt]" value="{{ $asset->get('alt') }}" />
                        </div>

                    </div>

                </div>
                <div class="publish-meta col-md-3">

                    <div class="form-group">
                        <img src="{{ $asset->manipulate()->square(100)->fit('crop')->build() }}" />
                    </div>

                    <div class="form-group">
                        <label class="block">Container</label>
                        <p class="form-control-static">{{ $asset->container()->title() }}</p>
                    </div>

                    <div class="form-group">
                        <label class="block">Path</label>
                        <p class="form-control-static">{{ $asset->getPath() }}</p>
                    </div>
                </div>
        </div>

    </form>

@endsection
