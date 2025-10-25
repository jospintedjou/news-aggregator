<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-docs', function () {
    $yaml = file_get_contents(base_path('docs/openapi.yaml'));
    return response($yaml, 200)
        ->header('Content-Type', 'application/x-yaml');
});
