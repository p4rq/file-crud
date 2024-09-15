<?php

use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// Fetch all files for listing in FileList.vue
Route::get('/files', [FileController::class, 'index'])->name('files.index');

// Create a new file (FileForm.vue for creation)
Route::post('/files', [FileController::class, 'store'])->name('files.store');


// Fetch a specific file for editing (FileForm.vue for editing)
Route::get('/files/{id}', [FileController::class, 'show'])->name('files.show');

// Update an existing file
Route::put('/files/{id}', [FileController::class, 'update'])->name('files.update');

// Delete a file
Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');

Route::get('/{pathMatch}', function () {
    return view('welcome');
})->where('pathMatch', '.*');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
