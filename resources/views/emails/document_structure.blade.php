@extends('emails.base')
@section('body-content')
<p>Madame, Monsieur,</p>

<p>{{ $intro }}</p>

<p>
    Vous trouverez en pièce jointe le document
    <strong>« {{ $document_nom }} »</strong>
    relatif à la demande <strong>{{ $code }}</strong>.
</p>

@if(!empty($comment))
<p>{{ $comment }}</p>
@endif

<p class="text-center">{{ SettingData('name') }}.</p>
@endsection
