@extends('emails.base')
@section('body-content')
<p>Cher(e) {{ $user?->name }},</p>

<p>Bienvenue sur notre plateforme !</p>
<p>Voici vos identifiants de connexion :</p>
<ul>
    <li><strong>Identifiant : </strong> {{ $user?->email }}</li>
    <li><strong>Mot de passe : </strong> {{ $password }}</li>
</ul>
<p>Pour accéder à votre espace de travail, veuillez cliquer sur le lien suivant:  <a href="{{ env('APP_FRONT_URL') }}">Connexion</a></p>
<p>Pour des raisons de sécurité, nous vous demanderons de modifier votre mot de passe lors de votre première connexion.</p>

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe FCT </p>

{{-- <p class="text-center"> {{ SettingData('name') }}, vous remercie.</p> --}}
@endsection

