@extends('layouts.site')

@section('title', 'Восстановление пароля')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="mx-auto max-w-sm space-y-4">
        @csrf
        <h1 class="text-xl font-semibold">Восстановление пароля</h1>
        <p class="text-sm">Укажите email, и мы отправим ссылку для сброса пароля.</p>

        @if (session('status'))
            <p class="text-sm text-green-700">{{ session('status') }}</p>
        @endif

        <label class="block text-sm">Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full">
            @error("email")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </label>

        <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Отправить ссылку</button>
    </form>
@endsection
