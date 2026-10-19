@extends('layouts.site')

@section('title', $house->name)

@section('content')
    <a href="{{ route('houses') }}" class="text-sm text-sea-700 hover:underline">← Все домики</a>

    <div class="mt-3 grid gap-8 md:grid-cols-2">
        <section>
            @if ($house->picture_url)
                <img src="{{ $house->picture_url }}" alt="" class="mb-4 h-56 w-full object-cover">
            @endif
            <h1 class="text-xl font-semibold">{{ $house->name }}</h1>
            <p class="mt-1 text-sm text-ink/70">{{ $house->houseType->name }}
                @if ($house->houseType->description) — {{ $house->houseType->description }} @endif
            </p>
            <dl class="mt-4 space-y-1 text-sm">
                <div>Вместимость: {{ $house->capacity }} чел.</div>
                <div>Час: {{ $house->price_hour }}</div>
                <div>Сутки: {{ $house->price_day }}</div>
            </dl>
        </section>

        <section>
            <h2 class="mb-3 font-semibold">Бронирование</h2>

            @guest
                <p class="text-sm">Чтобы забронировать, <a href="{{ route('login') }}" class="text-sea-700 underline">войдите</a>
                    или <a href="{{ route('register') }}" class="text-sea-700 underline">зарегистрируйтесь</a>.</p>
            @else
                <form data-api="/api/bookings" data-handler="booking" data-redirect="/bookings"
                      data-price-hour="{{ $house->price_hour }}" data-price-day="{{ $house->price_day }}"
                      class="space-y-4">
                    <input type="hidden" name="house_id" value="{{ $house->id }}">
                    <p data-error class="hidden text-sm text-red-700"></p>

                    <label class="block text-sm">Заезд
                        <input type="datetime-local" name="time_start" required class="mt-1 block w-full">
                    </label>
                    <label class="block text-sm">Выезд
                        <input type="datetime-local" name="time_end" required class="mt-1 block w-full">
                    </label>

                    @if ($addons->isNotEmpty())
                        <fieldset>
                            <legend class="text-sm">Дополнительно</legend>
                            <div class="mt-1 divide-y divide-sand-300 border border-sand-300 bg-sand-50">
                                @foreach ($addons as $addon)
                                    <div data-addon="{{ $addon->id }}" data-price="{{ $addon->price_hour }}"
                                         class="flex items-center gap-3 px-3 py-2 text-sm">
                                        <input type="checkbox" value="{{ $addon->id }}" id="addon-{{ $addon->id }}">
                                        <label for="addon-{{ $addon->id }}" class="mr-auto">
                                            {{ $addon->addon_name }}
                                            <span class="text-ink/60">· {{ $addon->price_hour }} за час</span>
                                            <span data-free class="block text-xs text-ink/60"></span>
                                        </label>
                                        <input type="number" min="1" value="1" class="w-16">
                                        @if ($addon->picture_url)
                                            <img src="{{ $addon->picture_url }}" alt="" class="mb-4 h-56 w-full object-cover">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-ink/60">Наличие появится после выбора дат.</p>
                        </fieldset>
                    @endif

                    <p data-estimate class="text-sm font-semibold"></p>

                    <button class="bg-sea-700 px-4 py-2 text-sm text-white hover:bg-sea-800">Забронировать</button>
                </form>
            @endguest
        </section>
    </div>

    <section class="mt-10">
        <h2 class="mb-3 font-semibold">Отзывы</h2>
        @forelse ($reviews as $review)
            <article class="mb-3 border-b border-sand-300 pb-3 text-sm">
                <div class="font-semibold">{{ $review->booking->user->name }} · {{ $review->rating }}/5</div>
                @if ($review->text)
                    <p class="mt-1">{{ $review->text }}</p>
                @endif
                <div class="mt-1 text-xs text-ink/60">{{ $review->review_date->format('d.m.Y') }}</div>
            </article>
        @empty
            <p class="text-sm text-ink/70">Отзывов пока нет.</p>
        @endforelse
    </section>
@endsection
