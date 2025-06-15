<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
    <title>{{SettingData('name')  }}</title>
    <style>
        .main{
            padding: 15px 5px;
            margin: 15px 5px;
        }
        p{
            text-align:justify;
        }
        footer{
            width: 100%;
        }
        footer p{
            display: inline-block;
            height:10px;
            margin-left:5px;
            margin-right:5px;
            font-size:10px;
        }
    </style>
</head>


<body>
    <header class="text-center">
        <img src= {{ asset("Images/logo.png")}}   alt="">
    </header>
   <section class="main">
    @yield('body-content')
   </section>
   <footer class="text-center">
    <p> {{ SettingData("address") }}</p>
    <p>République du Bénin</p>
    <br>
    <p>{{ SettingData('phone') }}</p>
   <p > <a class="fct-text-bleu-azur" target="_blank" href="{{SettingData('site')  }}">{{SettingData('site')  }}</a></p>
  </footer>
</body>
</html>
