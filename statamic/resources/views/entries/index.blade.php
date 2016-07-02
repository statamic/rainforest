@extends('layout')

@section('content')

    <entry-listing inline-template v-cloak
        get="{{ route('entries.get', $collection->path()) }}"
        delete="{{ route('entries.delete') }}"
        reorder="{{ route('entries.reorder') }}"
        sort="{{ $sort }}"
        sort-order="{{ $sort_order }}"
        :reorderable="{{ bool_str($collection->order() === 'number') }}"
        :can-delete="{{ bool_str(\Statamic\API\User::getCurrent()->can('collections:'.$collection->path().':delete')) }}">

        <div class="card">

            <div class="head">
                <h1>{{ $collection->title() }}</h1>

                <div>
                    @can("super")
                        <div class="btn-group">
                            <a href="{{ route('collection.edit', $collection->path()) }}" class="btn btn-default">{{ translate('cp.configure') }}</a>
                        </div>
                    @endcan
                    @can("collections:{$collection->path()}:create")
                        <div class="btn-group">
                            <a href="{{ route('entry.create', $collection->path()) }}" class="btn btn-primary">{{ translate('cp.create_entry_button') }}</a>
                        </div>
                    @endcan
                </div>
            </div>

            <hr>

            <template v-if="noItems">
                <div class="info-block">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.entries_empty_heading', ['type' => $collection->title()]) }}</h2>
                    <h3>{{ trans('cp.entries_empty') }}</h3>
                    @can("collections:{$collection->path()}:create")
                        <a href="{{ route('entry.create', $collection->path()) }}" class="btn btn-default btn-lg">{{ trans('cp.create_entry_button') }}</a>
                    @endcan
                </div>
            </template>

            <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>

        </div>

    </entry-listing>

@endsection
