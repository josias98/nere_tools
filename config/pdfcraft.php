<?php

return [
    'retention_days' => (int) env('PDFCRAFT_RETENTION_DAYS', 90),
    'max_file_kib' => (int) env('PDFCRAFT_MAX_FILE_KIB', 102400),
    'pages_path' => resource_path('pdfcraft-pages'),
];
