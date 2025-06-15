@extends('emails.pdfBase')

@section('body-title')
    <h1 class="text-center">{{ $title }}</h1>
@endsection

@section('body-content')
    <p>{{ $content }}</p>
@endsection

@section('body-conclusion')
    <p class="text-center">{{ $conclusion }}</p>
@endsection
