@extends(config('enso.settings.layout'))

@section('content')
    <div class="container is-fluid">
        <header class="is-clearfix">
            <h1 class="title">
                {{ $crud->getNameSingular() }} status:

                <span class="is-pulled-right">
                    <a href="{{ route('admin.mailer.campaigns.preview.show', $item->getKey()) }}" class="button is-info">Preview</a>
                    @if(!$item->hasBeenSent())
                        <a href="{{ route('admin.mailer.campaigns.users.index', $item->getKey()) }}" class="button is-info">Users</a>
                        <a href="{{ route('admin.mailer.campaigns.edit', $item->getKey()) }}" class="button is-info">Edit</a>
                    @elseif(Auth::check() && Auth::user()->hasPermission('enso-mailer-refresh-campaign'))
                        <a href="{{ route('admin.mailer.campaigns.status.refresh', $item->getKey()) }}" class="button is-info">Refresh Stats</a>
                    @endif
                    @include($crud->getCrudView('lists.edit-defaults'))
                </span>
            </h1>

            <h2 class="subtitle">{{ $item->getName() }}</h2>
        </header>

        <hr>

        @include('enso-crud::partials.alerts')

        @if($item->hasBeenSent())
          @include($crud->getCrudView('status.partials.campaign-details'))
        @else
          @include($crud->getCrudView('status.partials.ready-to-send'))
        @endif
    </div>
@endsection
