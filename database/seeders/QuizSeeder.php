<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['question' => 'Ibu kota Indonesia adalah ...', 'answer' => 'Jakarta'],
            ['question' => '2 + 2 = ...', 'answer' => '4'],
            ['question' => 'Warna bendera Indonesia adalah ...', 'answer' => 'Merah Putih'],
            ['question' => 'Gunung tertinggi di Indonesia adalah ...', 'answer' => 'Puncak Jaya'],
            ['question' => 'Hewan berkaki satu disebut ...', 'answer' => 'Tidak ada'],
        ];

        Quiz::insert($data);
    }
}

