<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    protected function storeImage(?UploadedFile $file, string $folder, ?string $old = null): ?string
    {
        if (! $file) {
            return $old;
        }
        if ($old && ! str_starts_with($old, 'demo/')) {
            Storage::disk('public')->delete($old);
        }
        return $file->store($folder, 'public');
    }
}
