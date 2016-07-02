@extends('layout')

@section('content')

    <addon-listing inline-template v-cloak>

        <div class="card">

            <div class="head">
                <h1>{{ trans('cp.nav_addons') }}</h1>

                <div class="btn-group">
                    <button @click="refresh" class="btn btn-default">{{ trans('cp.refresh') }}</button>
                </div>
            </div>

            <hr>

            <template v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-power-plug"></span>
                    <h2>{{ trans('cp.addons_empty_heading') }}</h2>
                    <h3>{{ trans('cp.addons_empty') }}</h3>
                </div>
            </template>

            <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>

        </div>

    </addon-listing>

@endsection
