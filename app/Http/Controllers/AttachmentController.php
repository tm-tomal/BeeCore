<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Stream an uploaded image/video back to an authorised workspace member.
     * Files live on the private disk, so serving goes through this route instead
     * of the public storage symlink.
     */
    public function __invoke(Request $request, Attachment $attachment)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $tenantContextId = app(CurrentTenant::class)->id();
        $allowed = $user->isSuperAdmin()
            || (int) $attachment->tenant_id === $tenantContextId
            || (int) ($user->tenant_id ?? 0) === (int) $attachment->tenant_id;

        abort_unless($allowed, 403);

        $disk = Storage::disk($attachment->disk ?: 'local');
        abort_unless($disk->exists($attachment->path), 404);

        $headers = [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Type' => $attachment->mime_type ?? $disk->mimeType($attachment->path),
        ];

        $fullPath = $disk->path($attachment->path);

        // Downloads force an attachment disposition; anything else streams
        // inline (Symfony's BinaryFileResponse answers Range requests, so
        // large videos can seek/play progressively).
        return $request->query('download')
            ? response()->download($fullPath, $attachment->original_name, $headers)
            : response()->file($fullPath, $headers);
    }
}
