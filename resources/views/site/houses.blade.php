@extends('layouts.site')

@section('title', 'Домики')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold">Домики</h1>

        <form method="get" class="flex items-center gap-2 text-sm">
            <label for="type">Тип</label>
            <select id="type" name="type" onchange="this.form.submit()">
                <option value="">Все</option>
                @foreach ($types as $t)
                    <option value="{{ $t->id }}" @selected((string) $type === (string) $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @forelse ($houses as $house)
        <a href="{{ route('houses.show', $house) }}"
           class="mb-3 flex gap-4 border border-sand-300 bg-sand-50 p-3 hover:border-sea-600">
            @if ($house->picture_url)
                <img src="{{ $house->picture_url }}" alt="" class="h-24 w-36 shrink-0 object-cover">
            @else
                <div class="h-24 w-36 shrink-0 bg-sea-100"></div>
            @endif
            <div class="min-w-0">
                <div class="font-semibold text-sea-800">{{ $house->name }}</div>
                <div class="text-sm text-ink/70">{{ $house->houseType->name }} · до {{ $house->capacity }} чел.</div>
                <div class="mt-2 text-sm">{{ $house->price_hour }} за час · {{ $house->price_day }} за сутки</div>
            </div>
        </a>
    @empty
        <p class="text-sm text-ink/70">Домиков пока нет.</p>
    @endforelse
@endsection
