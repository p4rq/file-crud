<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    // Upload or Create a new file
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:8192', // Max size 8MB
            'name' => 'nullable|string|max:255',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Store the file and create a new file record
        $file = $request->file('file');
        $filePath = $file->store('uploads', 'public'); // Store in 'storage/app/public/uploads'

        $storedFile = File::create([
            'name' => $request->name ?? $file->getClientOriginalName(),
            'path' => $filePath,
            'category' => $request->category,
            'size' => $file->getSize(),
        ]);

        return response()->json($storedFile, 201);
    }

    // Fetch a specific file by ID
    public function show($id)
    {
        $file = File::findOrFail($id);

        return response()->json($file, 200);
    }

    // List all files with pagination and optional search
    public function index(Request $request)
    {
        // Optional search functionality
        $searchQuery = $request->query('search');
        $filesQuery = File::query();

        if ($searchQuery) {
            $filesQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
        }

        // Paginate the results with 50 files per page
        $files = $filesQuery->paginate(50);

        return response()->json($files, 200);
    }

    // Update existing file metadata (not the file itself)
    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'category' => 'required|string',
            'file' => 'nullable|file|max:8192' // Ограничение на размер файла (8 MB)
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Обновление данных файла (название, категория)
        $file->update([
            'name' => $request->name ?? $file->name,
            'category' => $request->category,
        ]);

        // Если был загружен новый файл, заменяем его
        if ($request->hasFile('file')) {
            // Удаляем старый файл, если нужно (проверьте путь к файлу)
            Storage::delete($file->path);

            // Загружаем новый файл и обновляем путь
            $path = $request->file('file')->store('files');
            $file->update(['path' => $path]);
        }

        return response()->json($file, 200);
    }


    // Delete a file
    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // Delete the file from storage
        Storage::disk('public')->delete($file->path);

        // Delete the record from the database
        $file->delete();

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
