<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
    <title>{{SettingData('name')  }}</title>
    <style>

<style>
    @page {
        margin: 50px 25px; /* Marges pour éviter que le contenu ne chevauche l'en-tête et le pied de page */
    }

    @page :footer {
        content: "Page " counter(page) " sur " counter(pages); /* Numéro de page dynamique */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
        font-size:10px;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 20px;
        text-align:center;
    }

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
        .text-center{
            text-align:center;
        }

        .mb-1{
            margin-bottom:10px;
        }
        .mb-2{
            margin-bottom:20px;
        }

        .mb-3{
            margin-bottom:30px;
        }
        .mb-4{
            margin-bottom:40px;
        }

        .mb-1{
            margin-bottom:50px;
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
   <footer class="footer">
    <p> {{ SettingData("address") }}</p>
    <p>République du Bénin</p>
    <br>
    <p>{{ SettingData('phone') }}</p>
   <p > <a class="fct-text-bleu-azur" target="_blank" href="{{SettingData('site')  }}">{{SettingData('site')  }}</a></p>
  </footer>
</body>
</html>
