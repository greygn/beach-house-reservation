@extends('layouts.site')

@section('title', 'Мои брони')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Мои брони</h1>

    @forelse ($bookings as $booking)
        @php
            $isCancelled = $booking->status === $cancelled;
            $canReview = ! $isCancelled && $booking->time_end->isPast() && ! $booking->review;
        @endphp

        <article class="mb-4 border border-sand-300 bg-sand-50 p-4 text-sm">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <a href="{{ route('houses.show', $booking->house) }}" class="font-semibold text-sea-800 hover:underline">
                    {{ $booking->house->name }}
                </a>
                <span class="{{ $isCancelled ? 'text-red-700' : 'text-sea-700' }}">{{ $booking->status->label() }}</span>
            </div>

            <div class="mt-1">{{ $booking->time_start->format('d.m.Y H:i') }} — {{ $booking->time_end->format('d.m.Y H:i') }}</div>

            @if ($booking->addons->isNotEmpty())
                <div class="mt-1 text-ink/70">
                    Аддоны: {{ $booking->addons->map(fn ($a) => $a->addon_name.' × '.$a->pivot->quantity)->join(', ') }}
                </div>
            @endif

            <div class="mt-1">Стоимость: {{ $booking->total_price }}</div>

            @if (! $isCancelled && $booking->time_end->isFuture())
                <form data-api="/api/bookings/{{ $booking->id }}/cancel" data-method="PATCH"
                      data-confirm="Отменить бронь?" class="mt-2">
                    <button class="text-red-700 hover:underline">Отменить бронь</button>
                </form>
            @endif

            @if ($booking->review)
                <div class="mt-3 border-t border-sand-300 pt-3">
                    <div>Ваш отзыв: {{ $booking->review->rating }}/5 · <span class="text-ink/70">{{ $booking->review->status->name }}</span></div>
                    @if ($booking->review->text)
                        <p class="mt-1">{{ $booking->review->text }}</p>
                    @endif
                </div>
            @elseif ($canReview)
                <form data-api="/api/bookings/{{ $booking->id }}/review" class="mt-3 space-y-2 border-t border-sand-300 pt-3">
                    <p data-error class="hidden text-sm text-red-700"></p>
                    <label class="block">Оценка
                        <select name="rating" class="ml-2">
                            @foreach ([5, 4, 3, 2, 1] as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </label>
                    <textarea name="text" rows="3" maxlength="5000" placeholder="Отзыв" class="block w-full"></textarea>
                    <button class="bg-sea-700 px-3 py-1.5 text-white hover:bg-sea-800">Отправить отзыв</button>
                    <span class="text-xs text-ink/60">Отзыв появится после проверки.</span>
                </form>
            @endif
        </article>
    @empty
        <p class="text-sm text-ink/70">Броней пока нет. <a href="{{ route('houses') }}" class="text-sea-700 underline">Выбрать домик</a></p>
    @endforelse
@endsection
