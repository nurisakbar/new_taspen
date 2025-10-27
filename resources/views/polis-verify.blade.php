<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Validasi Dokumen Polis</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 50vh;
                margin: 0;
            }

            .full-height {
                height: 50vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content" style="margin-top:100px">
                <img src="{{asset('Taspen Life-Logo.png')}}" width="200">
               <h3 style="margin-top: 30px">
                    Validasi Dokumen Polis
               </h3>

               @if(session('message')!=null)
               <div class="alert alert-primary" role="alert">
                {{ session('message') }}
              </div>
              @endif

              <form action="/polis-verify/{{$id}}" method="POST">
                @csrf
                <p>Gunakan Tanggal Lahir dengan format DDMMYYYY<br>Sebagai Password (Contoh : 01121990) </p>
                <input maxlength="8" type="text" name="password" placeholder="Masukan Password Anda" class="form-control">
                <br>
                <button type="submit" class="btn btn-primary">Validasi Password</button>
               </form>
            </div>
        </div>
    </body>
</html>
