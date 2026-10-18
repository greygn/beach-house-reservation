@extends('layouts.admin')

@section('title', 'Домики')

@section('content')
    <form data-api="/api/admin/houses" data-multipart class="mb-8 flex flex-wrap items-end gap-3 text-sm">
        <p data-error class="hidden w-full text-red-700"></p>
        <label>Название<br><input type="text" name="name" required maxlength="255"></label>
        <label>Тип<br>
            <select name="house_type_id" required>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Мест<br><input type="number" name="capacity" min="1" required class="w-20"></label>
        <label>Час<br><input type="number" name="price_hour" min="0" step="0.01" required class="w-28"></label>
        <label>Сутки<br><input type="number" name="price_day" min="0" step="0.01" required class="w-28"></label>
        <label>Фото<br><input type="file" name="picture" accept="image/jpeg,image/png,image/webp"></label>
        <button class="bg-gray-900 px-4 py-1.5 text-white hover:bg-gray-700">Добавить</button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2 pr-3 font-medium">Название</th>
                    <th class="py-2 pr-3 font-medium">Тип</th>
                    <th class="py-2 pr-3 font-medium">Мест</th>
                    <th class="py-2 pr-3 font-medium">Час</th>
                    <th class="py-2 pr-3 font-medium">Сутки</th>
                    <th class="py-2 pr-3 font-medium">Фото</th>
                    <th class="py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($houses as $house)
                    @php $f = 'house-'.$house->id; @endphp
                    <tr class="border-b border-gray-200">
                        <td class="py-2 pr-3"><input form="{{ $f }}" type="text" name="name" value="{{ $house->name }}" required></td>
                        <td class="py-2 pr-3">
                            <select form="{{ $f }}" name="house_type_id">
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected($house->house_type_id === $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="py-2 pr-3"><input form="{{ $f }}" type="number" name="capacity" min="1" value="{{ $house->capacity }}" class="w-20"></td>
                        <td class="py-2 pr-3"><input form="{{ $f }}" type="number" name="price_hour" min="0" step="0.01" value="{{ $house->price_hour }}" class="w-28"></td>
                        <td class="py-2 pr-3"><input form="{{ $f }}" type="number" name="price_day" min="0" step="0.01" value="{{ $house->price_day }}" class="w-28"></td>
                        <td class="py-2 pr-3">
                            @if ($house->picture_url)
                                <img src="{{ $house->picture_url }}" alt="" class="mb-1 h-12 w-16 object-cover">
                            @endif
                            <input form="{{ $f }}" type="file" name="picture" accept="image/jpeg,image/png,image/webp" class="block w-48">
                            @if ($house->picture)
                                <label class="mt-1 block text-xs text-gray-600">
                                    <input form="{{ $f }}" type="checkbox" name="remove_picture" value="1"> убрать фото
                                </label>
                            @endif
                        </td>
                        <td class="py-2 whitespace-nowrap">
                            <form id="{{ $f }}" data-api="/api/admin/houses/{{ $house->id }}" data-method="PATCH" data-multipart class="inline">
                                <button class="text-gray-900 hover:underline">Сохранить</button>
                            </form>
                            <form data-api="/api/admin/houses/{{ $house->id }}" data-method="DELETE"
                                  data-confirm="Удалить домик «{{ $house->name }}»?" class="ml-3 inline">
                                <button class="text-red-700 hover:underline">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
