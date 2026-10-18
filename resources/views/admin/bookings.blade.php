@extends('layouts.admin')

@section('title', 'Брони')

@section('content')
    <form method="get" class="mb-5 flex flex-wrap items-end gap-3 text-sm">
        <label>Домик<br>
            <select name="house_id">
                <option value="">Все</option>
                @foreach ($houses as $house)
                    <option value="{{ $house->id }}" @selected((string) ($filters['house_id'] ?? '') === (string) $house->id)>{{ $house->name }}</option>
                @endforeach
            </select>
        </label>
        <label>С<br><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
        <label>По<br><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
        <label>Статус<br>
            <select name="status">
                <option value="">Все</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <button class="bg-gray-900 px-4 py-1.5 text-white hover:bg-gray-700">Показать</button>
        <a href="{{ route('admin.bookings') }}" class="py-1.5 text-gray-600 hover:text-gray-900">Сбросить</a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2 pr-4 font-medium">№</th>
                    <th class="py-2 pr-4 font-medium">Домик</th>
                    <th class="py-2 pr-4 font-medium">Гость</th>
                    <th class="py-2 pr-4 font-medium">Период</th>
                    <th class="py-2 pr-4 font-medium">Аддоны</th>
                    <th class="py-2 pr-4 font-medium">Сумма</th>
                    <th class="py-2 pr-4 font-medium">Статус</th>
                    <th class="py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 pr-4">{{ $booking->id }}</td>
                        <td class="py-2 pr-4">{{ $booking->house->name }}</td>
                        <td class="py-2 pr-4">{{ $booking->user->name }}<br><span class="text-gray-500">{{ $booking->user->phone }}</span></td>
                        <td class="py-2 pr-4 whitespace-nowrap">{{ $booking->time_start->format('d.m.Y H:i') }}<br>{{ $booking->time_end->format('d.m.Y H:i') }}</td>
                        <td class="py-2 pr-4">{{ $booking->addons->map(fn ($a) => $a->addon_name.' × '.$a->pivot->quantity)->join(', ') ?: '—' }}</td>
                        <td class="py-2 pr-4">{{ $booking->total_price }}</td>
                        <td class="py-2 pr-4">{{ $booking->status->label() }}</td>
                        <td class="py-2">
                            @if ($booking->status === \App\Enums\BookingStatus::Active && $booking->time_end->isFuture())
                                <form data-api="/api/admin/bookings/{{ $booking->id }}/cancel" data-method="PATCH"
                                      data-confirm="Отменить бронь №{{ $booking->id }}?">
                                    <button class="text-red-700 hover:underline">Отменить</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-4 text-gray-500">Броней не найдено.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
@endsection
