@extends('emails.base')
@section('body-content')
<p>Cher(e)  {{ $user?->name }}, </p>
    
<p> 
    Nous avons reçu une demande de réinitialisation de votre mot de passe. Pour procéder à cette réinitialisation, veuillez cliquer sur le lien ci-dessous :
</p>

<p><a href="{{ env('APP_FRONT_URL') }}/reset-password/{{$token}}">Lien de réinitialisation</a></p>
<p>Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet e-mail.</p>

{{-- <p class="text-center">  {{ SettingData('name') }}, vous remercie.</p> --}}

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe FCT </p>
@endsection

