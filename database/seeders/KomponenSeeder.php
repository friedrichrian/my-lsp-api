<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Komponen;

class KomponenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $komponens = [
            'Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen',
            'Saya memahami tujuan dan manfaat dari asesmen yang akan dilakukan',
            'Saya diberikan kesempatan untuk bertanya dan berdiskusi sebelum asesmen dimulai',
            'Saya merasa nyaman dengan lingkungan dan fasilitas yang disediakan',
            'Saya diberikan waktu yang cukup untuk menyelesaikan setiap tahap asesmen',
            'Saya merasa diperlakukan dengan adil dan profesional selama proses asesmen',
            'Saya mendapatkan feedback yang jelas dan konstruktif dari asesor',
            'Saya merasa proses asesmen sesuai dengan standar yang telah ditetapkan',
            'Saya diberikan kesempatan untuk menunjukkan kemampuan saya secara optimal',
            'Saya merasa puas dengan pelayanan yang diberikan oleh tim asesmen',
            'Saya memahami kriteria penilaian yang digunakan dalam asesmen',
            'Saya merasa proses asesmen berjalan transparan dan objektif',
            'Saya mendapatkan dukungan yang diperlukan selama proses asesmen',
            'Saya merasa hasil asesmen mencerminkan kemampuan saya yang sebenarnya',
            'Saya akan merekomendasikan program asesmen ini kepada orang lain'
        ];

        foreach ($komponens as $komponen) {
            Komponen::create([
                'komponen' => $komponen
            ]);
        }
    }
}
