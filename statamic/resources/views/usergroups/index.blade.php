@extends('layout')

@section('content')

    <user-group-listing inline-template v-cloak>

        <div class="card">

            <div class="head">
                <h1>{{ translate('cp.nav_user-groups') }}</h1>
                <a href="{{ route('user.group.create') }}" class="btn btn-primary">{{ translate('cp.create_usergroup_button') }}</a>
            </div>

            <hr>

            <template v-if="noItems" v-cloak>
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.usergroups_empty_heading') }}</h2>
                    <h3>{{ trans('cp.usergroups_empty') }}</h3>
                    <a href="{{ route('user.group.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_usergroup_button') }}</a>
                </div>
            </template>

            <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>

        </div>

    </user-group-listing>

@endsection
