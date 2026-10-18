@extends('layouts.admin')

@section('title', 'Отзывы')

@section('content')
    <form method="get" class="mb-5 flex items-end gap-3 text-sm">
        <label>Статус<br>
            <select name="status_id" onchange="this.form.submit()">
                <option value="">Все</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" @selected((string) $statusId === (string) $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 text-gray-600">
                    <th class="py-2 pr-4 font-medium">Дата</th>
                    <th class="py-2 pr-4 font-medium">Домик</th>
                    <th class="py-2 pr-4 font-medium">Автор</th>
                    <th class="py-2 pr-4 font-medium">Оценка</th>
                    <th class="py-2 pr-4 font-medium">Текст</th>
                    <th class="py-2 font-medium">Статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="py-2 pr-4 whitespace-nowrap">{{ $review->review_date->format('d.m.Y') }}</td>
                        <td class="py-2 pr-4">{{ $review->booking->house->name }}</td>
                        <td class="py-2 pr-4">{{ $review->booking->user->name }}</td>
                        <td class="py-2 pr-4">{{ $review->rating }}/5</td>
                        <td class="max-w-md py-2 pr-4">{{ $review->text }}</td>
                        <td class="py-2">
                            <form data-api="/api/admin/reviews/{{ $review->id }}/status" data-method="PATCH" class="flex gap-2">
                                <select name="status_id">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}" @selected($review->status_id === $status->id)>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                <button class="bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-700">Сохранить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-gray-500">Отзывов нет.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
@endsection
