<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Robot Simulator</title>

        <!-- Fonts -->
        

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    </head>
    <body>
        <section class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    {!! $markdown !!}
                </div>
            </div>
        </section>
    </body>
</html>
