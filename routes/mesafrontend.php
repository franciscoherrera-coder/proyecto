<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\MesaPublicaController;

Route::get('/mesasfront', [MesaPublicaController::class, 'index'])->name('frontend.mesas.index');
