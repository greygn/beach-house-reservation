<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Администрирование')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-gray-900">
    @php
        $nav = [
            'admin.bookings' => 'Брони',
            'admin.reviews' => 'Отзывы',
            'admin.houses' => 'Домики',
            'admin.house-types' => 'Типы домиков',
            'admin.addons' => 'Аддоны',
            'admin.statuses' => 'Статусы отзывов',
            'admin.users' => 'Пользователи',
        ];
    @endphp

    <div class="flex min-h-screen flex-col md:flex-row">
        <aside class="border-b border-gray-200 md:w-56 md:shrink-0 md:border-r md:border-b-0">
            <div class="px-4 py-4 text-sm font-semibold">Администрирование</div>
            <nav class="flex flex-wrap gap-x-4 gap-y-1 px-4 pb-4 md:block md:space-y-1 md:px-0 md:pb-0">
                @foreach ($nav as $name => $label)
                    <a href="{{ route($name) }}"
                       class="block text-sm md:px-4 md:py-1.5 {{ request()->routeIs($name) ? 'font-semibold text-gray-900 md:bg-gray-100' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('houses') }}" class="block text-sm text-gray-600 hover:text-gray-900 md:mt-4 md:px-4 md:py-1.5">На сайт</a>
                <form data-api="/logout" data-redirect="/" class="md:px-4 md:py-1.5">
                    <button class="text-sm text-gray-600 hover:text-gray-900">Выйти</button>
                </form>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 md:px-8">
            <h1 class="mb-5 text-xl font-semibold">@yield('title')</h1>
            <p id="page-error" class="mb-4 hidden border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
            @yield('content')
        </main>
    </div>
</body>
</html>
