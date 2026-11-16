@extends('layouts.site')

@section('title', 'Оплата')

@section('content')
    <h1 class="mb-2 text-xl font-semibold">Оплата брони №{{ $booking->id }}</h1>
    <p class="mb-6 text-sm text-ink/70">{{ $booking->house->name }} · {{ $booking->time_start->format('d.m.Y H:i') }} — {{ $booking->time_end->format('d.m.Y H:i') }}</p>

    @if ($booking->paid_at)
        <p class="text-sm">Бронь оплачена {{ $booking->paid_at->format('d.m.Y H:i') }}. <a href="{{ route('bookings') }}" class="text-sea-700 underline">Мои брони</a></p>
    @elseif ($booking->status !== \App\Enums\BookingStatus::Active)
        <p class="text-sm">Оплатить можно только подтверждённую бронь. <a href="{{ route('bookings') }}" class="text-sea-700 underline">Мои брони</a></p>
    @else
        <form data-api="/api/bookings/{{ $booking->id }}/pay" data-redirect="/bookings" autocomplete="off" class="max-w-sm space-y-3 text-sm">
            <p data-error class="hidden text-red-700"></p>
            <p class="font-semibold">К оплате: {{ $booking->total_price }}</p>
            <label class="block">Номер карты
                <input type="text" name="card_number" data-mask="card" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456" required class="mt-1 block w-full font-mono tracking-widest">
            </label>
            <label class="block">Владелец
                <input type="text" name="card_holder" data-mask="holder" required maxlength="100" placeholder="IVAN IVANOV" class="mt-1 block w-full uppercase tracking-wide">
            </label>
            <div class="flex gap-3">
                <label class="block">Срок (ММ/ГГ)
                    <input type="text" name="expiry" data-mask="expiry" inputmode="numeric" maxlength="5" placeholder="08/29" required class="mt-1 block w-full font-mono tracking-widest">
                </label>
                <label class="block">CVC
                    <input type="password" name="cvc" data-mask="cvc" inputmode="numeric" maxlength="3" placeholder="123" required class="mt-1 block w-full font-mono tracking-widest">
                </label>
            </div>
            <button class="bg-sea-700 px-4 py-2 text-white hover:bg-sea-800">Оплатить</button>
        </form>
    @endif
@endsection
