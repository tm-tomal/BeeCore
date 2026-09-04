<?php

namespace App\Support;

use App\Models\Attachment;
use App\Rules\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Stores attached images/videos that were already uploaded to the Livewire
 * temporary area (upload begins the moment the sender picks a file) onto the
 * private local disk and records an Attachment row for the owning model.
 */
class FileAttachments
{
    public const MAX_FILES = 6;

    /**
     * Per-file upload rule builder used inside Livewire validation arrays.
     *
     * @return array<int, array<int, string|MediaFile>>
     */
    public static function uploadRules(string $prefix = 'files'): array
    {
        return [
            $prefix => ['nullable', 'array', 'max:'.self::MAX_FILES],
            $prefix.'.*' => ['file', new MediaFile],
        ];
    }

    /**
     * Move Livewire temporary uploads to the private disk and attach them.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function attach(Model $attachable, array $files, int $tenantId, ?int $uploadedBy = null): int
    {
        $saved = 0;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $disk = Storage::disk('local');
            $directory = 'attachments/'.$tenantId.'/'.$attachable->getTable();
            $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
            $filename = Str::uuid()->toString().($extension ? '.'.$extension : '');

            $path = $file->storeAs($directory, $filename, ['disk' => 'local']);

            if (! $path) {
                continue;
            }

            Attachment::create([
                'tenant_id' => $tenantId,
                'attachable_type' => $attachable::class,
                'attachable_id' => $attachable->getKey(),
                'disk' => 'local',
                'path' => $path,
                'original_name' => basename((string) $file->getClientOriginalName()),
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                'size' => (int) $file->getSize(),
                'uploaded_by' => $uploadedBy,
            ]);

            $saved++;
        }

        return $saved;
    }
}
