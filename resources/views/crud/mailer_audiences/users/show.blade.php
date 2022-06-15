@extends(config('enso.settings.layout'))

@section('content')
    <div class="container is-fluid">
        <header class="is-clearfix">
            <h1 class="title">
                {{ $crud->getNameSingular() }} list

                <span class="is-pulled-right">
                    <a href="{{ route('admin.mailer.audiences.edit', $item->getKey()) }}" class="button is-info">Edit</a>
                    <a href="{{ route('admin.mailer.audiences.index') }}" class="button">Back</a>
                </span>
            </h1>

            <h2 class="subtitle">{{ $item->name }}</h2>
        </header>

        <hr>

        <ajax-table
            route="{{ route('admin.json.mailer.audiences.users.index', $item->getKey()) }}"
            :columns="{{ json_encode($columns) }}"
        ></ajax-table>
    </div>
@endsection
