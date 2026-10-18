@extends('layouts.admin')

@section('title', 'Типы домиков')

@section('content')
    <form data-api="/api/admin/house-types" class="mb-8 flex flex-wrap items-end gap-3 text-sm">
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
            @foreach ($types as $type)
                @php $f = 'type-'.$type->id; @endphp
                <tr class="border-b border-gray-200">
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="text" name="name" value="{{ $type->name }}" required></td>
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="text" name="description" value="{{ $type->description }}" class="w-full"></td>
                    <td class="py-2 whitespace-nowrap">
                        <form id="{{ $f }}" data-api="/api/admin/house-types/{{ $type->id }}" data-method="PATCH" class="inline">
                            <button class="hover:underline">Сохранить</button>
                        </form>
                        <form data-api="/api/admin/house-types/{{ $type->id }}" data-method="DELETE"
                              data-confirm="Удалить тип «{{ $type->name }}»?" class="ml-3 inline">
                            <button class="text-red-700 hover:underline">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
