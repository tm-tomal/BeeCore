<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire starts uploading a file the moment the sender picks it (before
    | the form is submitted) so large videos don't stall the "send" action.
    | The framework default caps each temporary file at 12 MB — we raise it to
    | 1 GB to match the image/video attachments sold to ISP workspaces.
    |
    | NOTE: PHP itself must also allow such bodies. On the web server raise
    | upload_max_filesize, post_max_size and max_execution_time accordingly.
    |
    */

    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:1048576'], // 1 GB per file
    ],

];
