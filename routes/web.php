<?php
//
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});
//
//// Fetch all files for listing in FileList.vue
//Route::get('/api/files', [FileController::class, 'index'])->name('files.index');
//
//// Create a new file (FileForm.vue for creation)
//Route::post('/api/files', [FileController::class, 'store'])->name('files.store');
//
//
//// Fetch a specific file for editing (FileForm.vue for editing)
//Route::get('/api/files/{id}', [FileController::class, 'show'])->name('files.show');
//
//// Update an existing file
//Route::put('/api/files/{id}', [FileController::class, 'update'])->name('files.update');
//
//// Delete a file
//Route::delete('/api/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');
//
//Route::get('/{pathMatch}', function () {
//    return view('welcome');
//})->where('pathMatch', '.*');
