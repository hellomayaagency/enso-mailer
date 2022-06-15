<div class="is-pulled-right">
    <a href="{{ route('admin.mailer.audiences.users.index', $item->getKey()) }}" class="button is-info">Preview</a>
    <a href="{{ route($crud->getRoute() . '.index') }}" class="button">Back</a>
</div>
