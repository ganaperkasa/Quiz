<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quiz', [QuizController::class, 'index']);
Route::post('/quiz/check', [QuizController::class, 'check'])->name('quiz.check');
