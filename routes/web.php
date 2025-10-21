<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;

// Route::get('/', function () {
//     return view('welcome');
// });



Route::get('/', [DiagnosisController::class, 'index']);
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->name('analyze');
Route::post('/reanalyze', [DiagnosisController::class, 'reanalyze'])->name('reanalyze');
