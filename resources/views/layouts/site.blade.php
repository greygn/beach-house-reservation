<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Пляжные домики')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-100 font-sans text-ink">
    <header class="border-b border-sand-300 bg-sand-50">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3">
            <a href="{{ route('houses') }}" class="mr-auto text-lg font-semibold text-sea-800">Пляжные домики</a>

            <a href="{{ route('houses') }}" class="text-sm hover:text-sea-700">Домики</a>

            @auth
                <a href="{{ route('bookings') }}" class="text-sm hover:text-sea-700">Мои брони</a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.bookings') }}" class="text-sm hover:text-sea-700">Администрирование</a>
                @endif
                <span class="text-sm text-ink/60">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-sea-700 hover:underline">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm hover:text-sea-700">Войти</a>
                <a href="{{ route('register') }}" class="text-sm hover:text-sea-700">Регистрация</a>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        <p id="page-error" class="mb-4 hidden border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        @yield('content')
    </main>
</body>
</html>
