@extends('layouts.admin')

@section('title', 'Аддоны')

@section('content')
    <form data-api="/api/admin/addons" data-multipart class="mb-8 flex flex-wrap items-end gap-3 text-sm">
        <p data-error class="hidden w-full text-red-700"></p>
        <label>Название<br><input type="text" name="addon_name" required maxlength="255"></label>
        <label>Количество<br><input type="number" name="quantity" min="0" required class="w-24"></label>
        <label>Цена за час<br><input type="number" name="price_hour" min="0" step="0.01" required class="w-28"></label>
        <label>Фото<br><input type="file" name="picture" accept="image/jpeg,image/png,image/webp"></label>
        <button class="bg-gray-900 px-4 py-1.5 text-white hover:bg-gray-700">Добавить</button>
    </form>

    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-gray-300 text-gray-600">
                <th class="py-2 pr-3 font-medium">Название</th>
                <th class="py-2 pr-3 font-medium">Количество</th>
                <th class="py-2 pr-3 font-medium">Цена за час</th>
                <th class="py-2 pr-3 font-medium">Фото</th>
                <th class="py-2 font-medium"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($addons as $addon)
                @php $f = 'addon-'.$addon->id; @endphp
                <tr class="border-b border-gray-200">
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="text" name="addon_name" value="{{ $addon->addon_name }}" required></td>
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="number" name="quantity" min="0" value="{{ $addon->quantity }}" class="w-24"></td>
                    <td class="py-2 pr-3"><input form="{{ $f }}" type="number" name="price_hour" min="0" step="0.01" value="{{ $addon->price_hour }}" class="w-28"></td>
                    <td class="py-2 pr-3">
                            @if ($addon->picture_url)
                                <img src="{{ $addon->picture_url }}" alt="" class="mb-1 h-12 w-16 object-cover">
                            @endif
                            <input form="{{ $f }}" type="file" name="picture" accept="image/jpeg,image/png,image/webp" class="block w-48">
                            @if ($addon->picture)
                                <label class="mt-1 block text-xs text-gray-600">
                                    <input form="{{ $f }}" type="checkbox" name="remove_picture" value="1"> убрать фото
                                </label>
                            @endif
                        </td>
                    <td class="py-2 whitespace-nowrap">
                        <form id="{{ $f }}" data-api="/api/admin/addons/{{ $addon->id }}" data-method="PATCH" data-multipart class="inline">
                            <button class="hover:underline">Сохранить</button>
                        </form>
                        <form data-api="/api/admin/addons/{{ $addon->id }}" data-method="DELETE"
                              data-confirm="Удалить аддон «{{ $addon->addon_name }}»?" class="ml-3 inline">
                            <button class="text-red-700 hover:underline">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
