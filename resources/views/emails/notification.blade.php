@extends('emails.base')
@section('body-content')
<p>Bonjour M./Mme.</p>

<p>{{$data}}</p>

<p class="text-center"> {{ SettingData('name') }}, vous remercie.</p>
@endsection

