<div class="card flush">
    <div class="head">
        <h1>{{ $title }}</h1>
    </div>
    <div class="card-body">
        <table class="control">
            @foreach($entries as $entry)
                <tr>
                    <td><a href="{{ $entry->editUrl() }}">{{ $entry->get('title') }}</a></td>
                    @if ($entry->orderType() === 'date')
                        <td>{{ $entry->date()->diffForHumans() }}</td>
                    @endif
                    <td class="text-right">
                        <a href="{{ $entry->url() }}">
                            <span class="icon icon-eye"></span>
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
