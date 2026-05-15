@extends('documents.pdfBase')

@section('body-title')
    <h2 style="font-size:14px; font-weight:bold; text-transform:uppercase; text-align:center;">
        {{ $title ?? 'AGRÉMENT' }}
    </h2>
    <p style="font-size:11px; text-align:center; margin:4px 0;">
        Cotonou, le {{ $date ?? '' }}
    </p>
@endsection

@section('body-content')
    <div style="font-size:11px; line-height:1.8; text-align:justify;">
        {!! $content ?? '' !!}
    </div>
@endsection

@section('conclusion')
    @if(!empty($conclusion))
        <p style="margin-top:20px; font-size:11px;">{!! $conclusion !!}</p>
    @endif
@endsection
