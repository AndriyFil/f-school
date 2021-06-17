<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Моя школа</title>

    <!-- Scripts -->

    <script src="{{ asset('js/app.js')  }}" defer></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css')  }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css')  }}" rel="stylesheet">
    <link rel="shortcut icon" href="/img/favicon.png" type="image/png">
</head>
<body>
    <v-app id="app" data-app="true">
{{--        <content-app></content-app>--}}
        @guest
            <welcome-app></welcome-app>
        @else
            <div class="app">
                <user-navbar :user_data='{!! Auth::user() !!}'></user-navbar>
                @if(Auth::user()->role == "teacher")
                <router-view
                    :subjects_prop='{!! $subjects !!}'
                    :classes_prop='{!! $classes  !!}'
                    :myclass='{!! $myclass  !!}'
                    :subjects_class='{!! $subjects_class  !!}'
                ></router-view>
                @endif
                @if(Auth::user()->role == "parent")
                    <parent-view
                        :schoolboys='{!! $schoolboys !!}'
                    ></parent-view>
                @endif
            </div>
        @endguest

        <main class="py-4">
            @yield('content')
        </main>
        @yield('email')
    </v-app>
</body>
</html>
