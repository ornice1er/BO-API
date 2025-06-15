<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
    <title>{{ SettingData('name') }}</title>
    <style>
        .main {
            padding: 50px 25px;
            margin: 50px 25px;
        }
    </style>
</head>

<body>
    <header class="text-center">
        <img src={{ asset("Images/logo.png") }} alt="">
    </header>
    <section class="main">
        <div class="title text-center">
            @yield('body-title')
        </div>

        <div class="content">
            @yield('body-content')
        </div>

        <div class="conclusion text-center">
            @yield('conclusion')
        </div>
    </section>
    <footer class="text-center">
        <p>{{ SettingData("address") }}</p>
        <p>République du Bénin</p>
        <p>{{ SettingData('phone') }}</p>
        <p><a class="fct-text-bleu-azur" target="_blank" href="{{ SettingData('site') }}">{{ SettingData('site') }}</a></p>
    </footer>
</body>
</html>
