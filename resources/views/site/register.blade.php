@extends('layouts.site')

@section('title', 'Регистрация')

@section('content')
    <form data-api="/register" data-redirect="/" class="mx-auto max-w-sm space-y-4">
        <h1 class="text-xl font-semibold">Регистрация</h1>
        <p data-error class="hidden text-sm text-red-700"></p>

        <label class="block text-sm">Имя
            <input type="text" name="name" required maxlength="255" class="mt-1 block w-full">
        </label>
        <label class="block text-sm">Email
            <input type="email" name="email" required class="mt-1 block w-full">
        </label>
        <label class="block text-sm">Телефон
            <input type="tel" name="phone" required maxlength="20" class="mt-1 block w-full">
        </label>
        <label class="block text-sm">Пароль
            <input type="password" name="password" required minlength="8" class="mt-1 block w-full">
        </label>
        <label class="block text-sm">Повторите пароль
            <input type="password" name="password_confirmation" required minlength="8" class="mt-1 block w-full">
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Зарегистрироваться</button>
        <p class="text-sm">Уже есть аккаунт? <a href="/login" class="text-sea-700 underline">Войти</a></p>
    </form>
@endsection
