@extends('layouts.app')

@section('content')
    <h1>Laravel Google OAuth2 PHPMailer</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @auth
        <p>Bienvenido, {{ auth()->user()->name }}!</p>
        @if (auth()->user()->googleToken)
            <p>¡Estás autenticado con Google!</p>
            <form action="{{ route('send.email') }}" method="POST">
                @csrf
                <button type="submit">Enviar correo de prueba</button>
            </form>
        @else
            <p>Haz clic para autenticarte con Google:</p>
            <a href="{{ route('auth.google') }}">Autenticar con Google</a>
        @endif
        <form action="{{ route('logout') }}" method="POST" style="margin-top: 10px;">
            @csrf
            <button type="submit" class="logout-btn">Cerrar sesión</button>
        </form>
    @else
        <p>Por favor, inicia sesión o regístrate para usar la autenticación con Google.</p>
        <a href="{{ route('login') }}">Iniciar sesión</a>
        <a href="{{ route('register') }}">Registrarse</a>
    @endauth
@endsection
