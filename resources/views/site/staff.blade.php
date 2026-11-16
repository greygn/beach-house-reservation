@extends('layouts.site')

@section('title', 'Мои смены')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Мои смены</h1>

    <h2 class="mb-2 font-semibold">Смены</h2>
    <ul class="mb-8 space-y-1 text-sm">
        @forelse ($shifts as $shift)
            <li>{{ $shift->time_start->format('d.m.Y H:i') }} — {{ $shift->time_end->format('d.m.Y H:i') }}</li>
        @empty
            <li class="text-ink/70">Смен нет.</li>
        @endforelse
    </ul>

    <h2 class="mb-2 font-semibold">Назначенные услуги</h2>
    @forelse ($assignments as $job)
        <article class="mb-3 border border-sand-300 bg-sand-50 p-3 text-sm">
            <div class="font-semibold">{{ $job->service->name }}</div>
            <div>{{ $job->time_start->format('d.m.Y H:i') }} — {{ $job->time_end->format('d.m.Y H:i') }}</div>
            <div>Место: {{ $job->booking->house->name }}</div>
            <div>Гостей: {{ $job->booking->guests }}</div>
        </article>
    @empty
        <p class="text-sm text-ink/70">Назначений нет.</p>
    @endforelse
@endsection
