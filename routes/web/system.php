<?php

use App\Http\Controllers\System\RunQueueWorkerController;
use Illuminate\Support\Facades\Route;

Route::get('/webcron/queue', RunQueueWorkerController::class)
    ->name('webcron.queue');
