<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SPA CATCH-ALL
|--------------------------------------------------------------------------
| All non-API routes fall through to the Vue app.
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');