<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\File;  // Обязательно импортируйте модель File
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {  // Генерируем 50 случайных записей
            File::create([
                'name' => $faker->word,  // Генерируем случайное название файла
                'path' => 'files/' . $faker->uuid . '.txt',  // Случайный путь к файлу
                'size' => $faker->randomFloat(2, 0.1, 8),  // Размер файла (0.1 до 8 MB)
                'extension' => 'txt',  // Расширение файла (можно сделать случайным)
            ]);

            // Генерация фейкового файла в хранилище (необязательно)
            Storage::put('files/' . $faker->uuid . '.txt', $faker->text);
        }
    }
}
