<?php
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/quiz-login', [ApiController::class, 'login']);
Route::post('/quiz-get-table', [ApiController::class, 'getTable']);
Route::post('/student-table', [ApiController::class, 'showTable']);