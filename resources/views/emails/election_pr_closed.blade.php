@extends('emails.base')
@section('body-content')
<p>Bonjour M./Mme {{$notifiable->name}}</p>


<p>
{{$content}}
</p>


<p>Pour acceder à la page de connexion, veuillez cliquer  <a href="{{ env('APP_FRONT_URL') }}">ici</a></p>
<br>
<p class="text-center"> {{ SettingData('name') }}, vous remercie.</p>
@endsection

