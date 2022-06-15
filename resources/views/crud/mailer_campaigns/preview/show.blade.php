@extends(config('enso.settings.layout'))

@section('content')
    <div class="container is-fluid">
        <header class="is-clearfix">
            <h1 class="title">
                {{ $crud->getNameSingular() }} preview

                <span class="is-pulled-right">
                    <a href="{{ route('admin.mailer.campaigns.status.show', $item->getKey()) }}" class="button is-info">Status</a>
                    @if(!$item->hasBeenSent())
                        <a href="{{ route('admin.mailer.campaigns.users.index', $item->getKey()) }}" class="button is-info">Users</a>
                        <a href="{{ route('admin.mailer.campaigns.edit', $item->getKey()) }}" class="button is-info">Edit</a>
                    @elseif(Auth::check() && Auth::user()->hasPermission('enso-mailer-refresh-campaign'))
                        <a href="{{ route('admin.mailer.campaigns.status.refresh', $item->getKey()) }}" class="button is-info">Refresh Stats</a>
                    @endif
                    @include($crud->getCrudView('lists.edit-defaults'))
                </span>
            </h1>

            <h2 class="subtitle">{{ $item->name }}</h2>
        </header>

        <hr>

        <page-previewer
            source="{{ route('admin.mailer.campaigns.email.show', $item->getKey()) }}"
        ></page-previewer>
    </div>
@endsection
