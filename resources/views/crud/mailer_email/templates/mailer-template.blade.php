@extends('enso-crud::mailer_email.base-template')

@section('email-title')

  @include('enso-crud::mailer_email.partials.title')

@endsection

@section('email-body')
@foreach($mailable->unpackedEmailBody() as $row)
@include('enso-crud::mailer_email.rows.' . $row->row_type, [
    'first_row' => (!($mailable->hasMailableTitle() || $mailable->hasMailableDate()) && $loop->first)
])
@endforeach
@endsection
