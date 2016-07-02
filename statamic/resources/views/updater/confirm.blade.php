@extends('layout')

@section('content')

<updater version="{{ $version }}" inline-template>
    <div class="card update" v-cloak>
        <div class="info-blocks">

                <h2>{{ $title }} to Statamic v{{ $version }}</h2>

                <template v-if="! started">
                    <h3>This is a safe process. We back up everything during running the update so you can always find your most recent version in <code>local/temp</code> if something goes wrong.</h3>
                    <a href="" @click.prevent="start" class="btn btn-default btn-lg">{{ $title }}</a>
                </template>

                <dl v-if="started">
                    <dt>Backup</dt>
                        <dd v-if="backingUp">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Backing up...
                            <small class="help-block">We're zipping up your <code>statamic</code> folder, just in case you need to roll back.</small>
                        </dd>
                        <dd v-if="backedUp">
                            <span class="icon icon-check text-success"></span>
                            Backed up.
                            <small class="help-block">@{{{ backupMessage }}}</small>
                        </dd>
                        <dd v-if="backupFailed" class="text-danger">
                            <span class="icon icon-cross"></span>
                            Backup Failed
                        </dd>

                    <dt>Getting v{{ $version }}</dt>
                        <dd v-if="downloading">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Downloading...
                            <small class="help-block">Getting the latest and greatest version of Statamic for you.</small>
                        </dd>
                        <dd v-if="downloaded">
                            <span class="icon icon-check text-success"></span>
                            Downloaded.
                            <small class="help-block">@{{{ downloadMessage }}}</small>
                        </dd>
                        <dd v-if="downloadFailed" class="text-danger">
                            <span class="icon icon-cross"></span>
                            Download Failed
                        </dd>

                    <dt v-if="!hasErrors || cleanupFailed">Installation</dt>
                    <dt v-if="hasErrors && !cleanupFailed" class="text-danger">Installation has failed</dt>
                        <dd v-if="!installing && !hasErrors" class="no-icon">
                            Installation will begin once the backup and download have completed.
                        </dd>

                        <dd v-if="unzipping">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Unzipping files...
                            <small class="help-block">Placing the files from the Statamic zip in a temporary location.</small>
                        </dd>
                        <dd v-if="unzipped">
                            <span class="icon icon-check text-success"></span>
                            Files unzipped.
                        </dd>

                        <dd v-if="installingDependencies">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Installing Dependencies...
                            <small class="help-block">Any addons with dependencies will need to be fetched. This may take a moment.</small>
                        </dd>
                        <dd v-if="installedDependencies">
                            <span class="icon icon-check text-success"></span>
                            Dependencies installed.
                        </dd>

                        <dd v-if="swapping">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Swapping files...
                            <small class="help-block">Your smelly old Statamic files are being swapped for sparkly clean new ones.</small>
                        </dd>
                        <dd v-if="swapped">
                            <span class="icon icon-check text-success"></span>
                            Files swapped.
                        </dd>

                        <dd v-if="updated">
                            <span class="icon icon-check text-success"></span>
                            <p v-if="updated" class="text-success">You're now running Statamic v{{ $version }}.</p>
                        </dd>

                        <dd v-if="cleaningUp">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            Cleaning up...
                            <small class="help-block">We're deleting the temporary files created during the update.</small>
                        </dd>
                        <dd v-if="cleanedUp">
                            <span class="icon icon-check text-success"></span>
                            <b>Update complete.</b>
                        </dd>

                        <dd v-if="hasErrors" v-for="error in errors" class="text-danger">
                            <span class="icon icon-cross"></span>
                            @{{ error.message }}
                            <small class="help-block" v-if="error.e">
                                @{{ error.e }}
                            </small>
                        </dd>
                    </dd>
                </dl>

                <a v-if="updated" href="{{ route('dashboard') }}" class="btn btn-lg">Return to Dashboard</a>

                <template v-if="updated">
                    <audio autoplay="true">
                        <source src="http://d.pr/a/zXcF+" type="audio/mp3">
                    </audio>
                </template>

        </div>
    </div>
</updater>

@endsection
