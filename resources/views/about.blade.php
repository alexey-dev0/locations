@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">Locations</h1>
            <div>
                This app provides you with a system of manipulating your locations.
            </div>
            <div>
                You can ADD, EDIT and REMOVE your custom locations with the power of YANDEX MAPS.
            </div>
            <div>
                Feel safely to store your locations. We grant full private access.
            </div>
            <p>
                @auth
                    <a href="{{ url('/locations') }}" class="btn btn-primary my-2">Get started</a>
                @endauth
                @guest
                    <a href="{{ url('/login') }}" class="btn btn-primary my-2">Login</a>
{{--                    <a href="{{ url('/register') }}" class="btn btn-secondary my-2">Sign in</a>--}}
                @endguest
            </p>
        </div>
    </div>
@endsection
