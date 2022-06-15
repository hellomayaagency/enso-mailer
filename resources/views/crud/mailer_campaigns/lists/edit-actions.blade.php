<div class="is-pulled-right">
    <a href="{{ route('admin.mailer.campaigns.preview.show', $item->getKey()) }}" class="button is-info">Preview</a>
    <a href="{{ route('admin.mailer.campaigns.status.show', $item->getKey()) }}" class="button is-info">Status</a>

    @if(!$item->hasBeenSent())
        <a href="{{ route('admin.mailer.campaigns.users.index', $item->getKey()) }}" class="button is-info">Users</a>
    @elseif(Auth::check() && Auth::user()->hasPermission('enso-mailer-refresh-campaign'))
        <a href="{{ route('admin.mailer.campaigns.status.refresh', $item->getKey()) }}" class="button is-info">Refresh Stats</a>
    @endif
    @include($crud->getCrudView('lists.edit-defaults'))
</div>
