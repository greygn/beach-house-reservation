<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesPictures
{
    // Same rules for every uploaded picture.
    protected function pictureRules(): array
    {
        return [
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_picture' => ['sometimes', 'boolean'],
        ];
    }

    
    protected function applyPicture(Request $request, string $dir, ?string $current, array $data): array
    {
        unset($data['remove_picture'], $data['picture']);

        $disk = Storage::disk('public');

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store($dir, 'public');
            if ($current) {
                $disk->delete($current);
            }
        } elseif ($request->boolean('remove_picture')) {
            $data['picture'] = null;
            if ($current) {
                $disk->delete($current);
            }
        }

        return $data;
    }
}
