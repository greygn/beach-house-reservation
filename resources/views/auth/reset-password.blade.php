@extends('layouts.site')

@section('title', 'Новый пароль')

@section('content')
    <form method="POST" action="{{ route('password.store') }}" class="mx-auto max-w-sm space-y-4">
        @csrf
        <h1 class="text-xl font-semibold">Новый пароль</h1>
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label class="block text-sm">Email
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="mt-1 block w-full">
            @error("email")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Пароль
            <input type="password" name="password" required autocomplete="new-password" class="mt-1 block w-full">
            @error("password")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>
        <label class="block text-sm">Повторите пароль
            <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 block w-full">
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Сменить пароль</button>
    </form>
@endsection
