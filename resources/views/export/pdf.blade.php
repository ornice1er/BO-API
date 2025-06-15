@extends('export.base')
@section('body-content')
<h2 class="text-center">{{$title}}</h1>
<h4 class="text-center">{{$subtitle}}</h1>
<p class="text-center">{{$description}}</p>

@if(isset($content))

<p class="text-center">{{$content}}</p>

@else

<table>
    <thead>
        @foreach($table_header as $th)
        <th>{{$th}}</th>
        @endforeach
    </thead>

    <tbody>
        @foreach($table_body as $tb)
        <tr>
            @foreach($tb as $key=>$value)
            <td>{{$value}}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

@endif

<p class="text-center">{{$conclusion}}</p>

@endsection
