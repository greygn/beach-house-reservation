@extends('layouts.admin')

@section('title', 'Статусы отзывов')

@section('content')
    <form data-api="/api/admin/statuses" class="mb-8 flex flex-wrap items-end gap-3 text-sm">
        <p data-error class="hidden w-full text-red-700"></p>
        <label>Название<br><input type="text" name="name" required maxlength="255"></label>
        <label>Описание<br><input type="text" name="description" class="w-80"></label>
        <button class="bg-gray-900 px-4 py-1.5 text-white hover:bg-gray-700">Добавить</button>
    </form>

    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-gray-300 text-gray-600">
                <th class="py-2 pr-3 font-medium">Название</th>
                <th class="py-2 pr-3 font-medium">Описание</th>
                <th class="py-2 font-medium"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statuses as $status)
                @php
                    $f = 'status-'.$status->id;
                    $isSystem = in_array($status->name, $system, true);
                @endphp
                <tr class="border-b border-gray-200">
                    <td class="py-2 pr-3">
                        <input form="{{ $f }}" type="text" name="name" value="{{ $status->name }}" required
                               @disabled($isSystem) class="disabled:bg-gray-100">
                    </td>
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="text" name="description" value="{{ $status->description }}" class="w-full"></td>
                    <td class="py-2 whitespace-nowrap">
                        <form id="{{ $f }}" data-api="/api/admin/statuses/{{ $status->id }}" data-method="PATCH" class="inline">
                            <button class="hover:underline">Сохранить</button>
                        </form>
                        @unless ($isSystem)
                            <form data-api="/api/admin/statuses/{{ $status->id }}" data-method="DELETE"
                                  data-confirm="Удалить статус «{{ $status->name }}»?" class="ml-3 inline">
                                <button class="text-red-700 hover:underline">Удалить</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="mt-3 text-xs text-gray-500">Системные статусы нельзя переименовать или удалить.</p>
@endsection
