@extends('layout')

@section('content')

    <user-listing inline-template v-cloak>

        <div class="card">

            <div class="head">
                <h1>{{ translate('cp.nav_users') }}</h1>
                @can('users:create')
                    <a href="{{ route('user.create') }}" class="btn btn-primary">{{ translate('cp.create_user_button') }}</a>
                @endcan
            </div>

            <hr>

            <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>

        </div>

    </user-listing>

@endsection
