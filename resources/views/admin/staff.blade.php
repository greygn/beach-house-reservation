@extends('layouts.admin')

@section('title', 'Сотрудники')

@section('content')
    <form data-api="/api/admin/staff" class="mb-8 flex flex-wrap items-end gap-3 text-sm">
        <p data-error class="hidden w-full text-red-700"></p>
        <label>Имя<br><input type="text" name="name" required maxlength="255"></label>
        <label>Email<br><input type="email" name="email" required maxlength="255"></label>
        <label>Телефон<br><input type="text" name="phone" required maxlength="20" class="w-36"></label>
        <label>Пароль<br><input type="password" name="password" required minlength="8"></label>
        <button class="bg-gray-900 px-4 py-1.5 text-white hover:bg-gray-700">Создать</button>
    </form>

    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-gray-300 text-gray-600">
                <th class="py-2 pr-4 font-medium">Сотрудник</th>
                <th class="py-2 pr-4 font-medium">Предстоящие смены</th>
                <th class="py-2 font-medium">Новая смена</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staff as $member)
                <tr class="border-b border-gray-200 align-top">
                    <td class="py-2 pr-4">{{ $member->name }}<br><span class="text-gray-500">{{ $member->email }}</span></td>
                    <td class="py-2 pr-4">
                        @forelse ($member->shifts as $shift)
                            <div class="whitespace-nowrap">
                                {{ $shift->time_start->format('d.m.Y H:i') }} — {{ $shift->time_end->format('d.m.Y H:i') }}
                                <form data-api="/api/admin/shifts/{{ $shift->id }}" data-method="DELETE"
                                      data-confirm="Удалить смену?" class="ml-2 inline">
                                    <button class="text-red-700 hover:underline">Удалить</button>
                                </form>
                            </div>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td class="py-2">
                        <form data-api="/api/admin/shifts" class="flex flex-wrap items-end gap-2">
                            <input type="hidden" name="user_id" value="{{ $member->id }}">
                            <input type="datetime-local" name="time_start" required>
                            <input type="datetime-local" name="time_end" required>
                            <button class="hover:underline">Добавить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
