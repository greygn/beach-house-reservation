@extends('layouts.site')

@section('title', 'Вход')

@section('content')
    <form data-api="/login" data-redirect="/" class="mx-auto max-w-sm space-y-4">
        <h1 class="text-xl font-semibold">Вход</h1>
        <p data-error class="hidden text-sm text-red-700"></p>

        <label class="block text-sm">Email
            <input type="email" name="email" required autofocus class="mt-1 block w-full">
        </label>
        <label class="block text-sm">Пароль
            <input type="password" name="password" required class="mt-1 block w-full">
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Войти</button>
        <p class="text-sm">Нет аккаунта? <a href="/register" class="text-sea-700 underline">Регистрация</a></p>
    </form>
@endsection
