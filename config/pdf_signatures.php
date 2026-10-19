<?php

return [
    'mode' => env('PDF_SIGNATURE_MODE', 'disabled'), // disabled, test, production
    'certificate_path' => env('PDF_SIGNATURE_CERTIFICATE_PATH'),
    'password' => env('PDF_SIGNATURE_PASSWORD'),
    'max_upload_kb' => 10240,
    'max_pages' => 100,
];
