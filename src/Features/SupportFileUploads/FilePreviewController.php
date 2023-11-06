<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Drawer\Utils;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class FilePreviewController implements HasMiddleware
{
    public static function middleware()
    {
        return array_map(fn ($middleware) => new Middleware($middleware), (array) FileUploadConfiguration::middleware());
    }

    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        return Utils::pretendResponseIsFile(
            FileUploadConfiguration::storage()->path(FileUploadConfiguration::path($filename)),
            FileUploadConfiguration::mimeType($filename)
        );
    }
}
