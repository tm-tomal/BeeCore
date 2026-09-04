<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

/**
 * Accepts only images and videos, up to 1 GB per file. The sender is expected
 * to share photos/videos of an issue (a cable cut, a router, a bill receipt),
 * so we deliberately reject documents, archives and other binaries.
 */
class MediaFile implements Rule
{
    public const MAX_BYTES = 1073741824; // 1 GB

    /** @var string */
    protected $message = 'Only images and videos up to 1 GB can be attached.';

    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile) {
            return false;
        }

        $mime = (string) ($value->getMimeType() ?: $value->getClientMimeType());

        $isMedia = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/');

        if (! $isMedia) {
            $this->message = 'Only images and videos can be attached — other file types are not supported.';

            return false;
        }

        if ($value->getSize() > self::MAX_BYTES) {
            $this->message = 'Each file must be 1 GB or smaller.';

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}
