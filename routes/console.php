<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Madrasah maju, bermutu, dan mendunia.');
})->purpose('Display an inspiring quote');
