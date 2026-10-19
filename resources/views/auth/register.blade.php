@extends('layouts.site')

@section('title', 'Регистрация')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="mx-auto max-w-sm space-y-4">
        @csrf
        <h1 class="text-xl font-semibold">Регистрация</h1>

        <label class="block text-sm">Имя
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autofocus autocomplete="name" class="mt-1 block w-full">
            @error("name")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Email
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 block w-full">
            @error("email")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Телефон
            <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" class="mt-1 block w-full">
            @error("phone")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Пароль
            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1 block w-full">
            @error("password")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Повторите пароль
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-1 block w-full">
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Зарегистрироваться</button>
        <p class="text-sm">Уже есть аккаунт? <a href="{{ route('login') }}" class="text-sea-700 underline">Войти</a></p>
    </form>
@endsection
