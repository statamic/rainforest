@extends('layout')

@section('content')

    <user-role-listing inline-template v-cloak>

        <div class="card">

            <div class="head">
                <h1>{{ translate('cp.nav_user-roles') }}</h1>
                <a href="{{ route('user.role.create') }}" class="btn btn-primary">{{ translate('cp.create_role_button') }}</a>
            </div>

            <hr>

            <template v-if="noItems" v-cloak>
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ translate('cp.roles_empty_heading') }}</h2>
                    <h3>{{ translate('cp.roles_empty') }}</h3>
                    <a href="{{ route('user.role.create') }}" class="btn btn-default btn-lg">{{ translate('cp.create_role_button') }}</a>
                </div>
            </template>

            <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>

        </div>

    </user-role-listing>

@endsection
