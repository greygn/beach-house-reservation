@extends('layouts.admin')

@section('title', 'Пользователи')

@section('content')
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2 pr-4 font-medium">Имя</th>
                    <th class="py-2 pr-4 font-medium">Email</th>
                    <th class="py-2 pr-4 font-medium">Телефон</th>
                    <th class="py-2 pr-4 font-medium">Администратор</th>
                    <th class="py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $f = 'user-'.$user->id;
                        $self = $user->is(auth()->user());
                    @endphp
                    <tr class="border-b border-gray-200">
                        <td class="py-2 pr-4">{{ $user->name }}</td>
                        <td class="py-2 pr-4">{{ $user->email }}</td>
                        <td class="py-2 pr-4">{{ $user->phone }}</td>
                        <td class="py-2 pr-4">
                            <input form="{{ $f }}" type="checkbox" name="is_admin" @checked($user->is_admin) @disabled($self)>
                        </td>
                        <td class="py-2 whitespace-nowrap">
                            <form id="{{ $f }}" data-api="/api/admin/users/{{ $user->id }}" data-method="PATCH" class="inline">
                                <button class="hover:underline">Сохранить</button>
                            </form>
                            @unless ($self)
                                <form data-api="/api/admin/users/{{ $user->id }}" data-method="DELETE"
                                      data-confirm="Удалить пользователя {{ $user->name }}?" class="ml-3 inline">
                                    <button class="text-red-700 hover:underline">Удалить</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
