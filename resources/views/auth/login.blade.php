@extends('layouts.site')

@section('title', 'Вход')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="mx-auto max-w-sm space-y-4">
        @csrf
        <h1 class="text-xl font-semibold">Вход</h1>

        @if (session('status'))
            <p class="text-sm text-green-700">{{ session('status') }}</p>
        @endif

        <label class="block text-sm">Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1 block w-full">
            @error("email")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Пароль
            <input type="password" name="password" required autocomplete="current-password" class="mt-1 block w-full">
            @error("password")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember"> Запомнить меня
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Войти</button>
        <p class="text-sm"><a href="{{ route('password.request') }}" class="text-sea-700 underline">Забыли пароль?</a></p>
        <p class="text-sm">Нет аккаунта? <a href="{{ route('register') }}" class="text-sea-700 underline">Регистрация</a></p>
    </form>
@endsection
