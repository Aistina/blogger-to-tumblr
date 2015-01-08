<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Blogger To Tumblr</title>

        <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    </head>

    <body>
        <div class="header">
            <div class="btb-menu pure-menu pure-menu-horizontal pure-menu-fixed">
                <a class="pure-menu-heading" href="">Blogger-To-Tumblr</a>
            </div>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="buttons">
            @yield('buttons')
        </div>
    </body>
</html>
