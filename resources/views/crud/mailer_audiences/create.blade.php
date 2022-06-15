@extends(config('enso.settings.layout'))

@section('content')
    <div class="container is-fluid">
        <header class="is-clearfix">
            <h1 class="title is-pulled-left">Create a new {{ $crud->getNameSingular() }}</h1>

            @include($crud->getCrudView('lists.create-actions'))
        </header>

        @include($crud->getCrudView('lists.create-head'))

        @include('enso-crud::partials.alerts')

        <audience-form
            method="POST"
            action="{{ route('admin.mailer.audiences.store') }}"
            :item="{}"
            :field-selection-options="{{ EnsoMailer::getFormDataJson() }}"
        ></audience-form>
    </div>
@endsection
