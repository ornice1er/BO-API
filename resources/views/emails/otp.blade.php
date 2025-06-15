@extends('emails.base')
@section('body-content')
<p>Cher(e) {{ $user?->name }},</p>

<p>Voici votre code de vérification pour finaliser votre connexion :</p>
<p><strong>{{$code}}</strong></p>

<p>Ce code est valable pendant 10 minutes. Ne le partagez avec personne.</p>
<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe FCT </p>
{{-- <p class="text-center"> {{ SettingData('name') }}, vous remercie.</p> --}}
@endsection

