@extends(config('enso.settings.layout'))

@section('content')
    <div class="container is-fluid">
        <header class="is-clearfix">
            <h1 class="title is-pulled-left">Edit a {{ $crud->getNameSingular() }} ({{ $item->user_count }})</h1>

            @include($crud->getCrudView('lists.edit-actions'))
        </header>

        @include($crud->getCrudView('lists.edit-head'))

        @include('enso-crud::partials.alerts')

        <audience-form
            method="PATCH"
            action="{{ route('admin.mailer.audiences.update', $item->getKey()) }}"
            :item="{{ json_encode($item->getFormState()) }}"
            :field-selection-options="{{ EnsoMailer::getFormDataJson() }}"
        ></audience-form>
    </div>
@endsection
