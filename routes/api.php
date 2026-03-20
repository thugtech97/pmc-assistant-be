<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\KnowledgeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/chat', [ChatController::class, 'chat']);
Route::get('/faqs', [ChatController::class, 'index']);

Route::get('/knowledge/types', [KnowledgeController::class, 'types']);
Route::apiResource('knowledge', KnowledgeController::class);