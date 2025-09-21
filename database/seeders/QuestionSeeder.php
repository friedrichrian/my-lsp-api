<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Questions for RPL Schema (skema_id = 1)
        $rplQuestions = [
            [
                'skema_id' => 1,
                'question_text' => 'Apa yang dimaksud dengan struktur data array?',
                'option_a' => 'Kumpulan data yang tersimpan dalam memori secara berurutan',
                'option_b' => 'Kumpulan data yang tersimpan dalam memori secara acak',
                'option_c' => 'Kumpulan data yang tersimpan dalam database',
                'option_d' => 'Kumpulan data yang tersimpan dalam file',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 1,
                'question_text' => 'Manakah yang merupakan keuntungan dari struktur data stack?',
                'option_a' => 'Akses data yang cepat di tengah-tengah',
                'option_b' => 'Implementasi LIFO (Last In First Out)',
                'option_c' => 'Penyimpanan data yang tidak terbatas',
                'option_d' => 'Operasi insert dan delete yang kompleks',
                'correct_option' => 'B'
            ],
            [
                'skema_id' => 1,
                'question_text' => 'Apa kompleksitas waktu untuk operasi insert pada array?',
                'option_a' => 'O(1)',
                'option_b' => 'O(log n)',
                'option_c' => 'O(n)',
                'option_d' => 'O(n²)',
                'correct_option' => 'A'
            ]
        ];

        // Questions for TBG Schema (skema_id = 2)
        $tbgQuestions = [
            [
                'skema_id' => 2,
                'question_text' => 'Apa yang dimaksud dengan food safety dalam tata boga?',
                'option_a' => 'Keamanan pangan untuk mencegah penyakit',
                'option_b' => 'Kemasan makanan yang menarik',
                'option_c' => 'Harga makanan yang terjangkau',
                'option_d' => 'Rasa makanan yang enak',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 2,
                'question_text' => 'Suhu berapa yang ideal untuk menyimpan daging di freezer?',
                'option_a' => '-2°C sampai -5°C',
                'option_b' => '0°C sampai 2°C',
                'option_c' => '2°C sampai 4°C',
                'option_d' => '4°C sampai 6°C',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 2,
                'question_text' => 'Apa fungsi garam dalam proses pengolahan makanan?',
                'option_a' => 'Memberikan rasa asin dan mengawetkan',
                'option_b' => 'Membuat makanan lebih manis',
                'option_c' => 'Membuat makanan lebih pedas',
                'option_d' => 'Membuat makanan lebih asam',
                'correct_option' => 'A'
            ]
        ];

        // Questions for PH Schema (skema_id = 3)
        $phQuestions = [
            [
                'skema_id' => 3,
                'question_text' => 'Apa yang dimaksud dengan check-in dalam perhotelan?',
                'option_a' => 'Proses pendaftaran tamu saat masuk hotel',
                'option_b' => 'Proses pembersihan kamar',
                'option_c' => 'Proses pembayaran tagihan',
                'option_d' => 'Proses keluar dari hotel',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 3,
                'question_text' => 'Apa yang harus dilakukan saat tamu mengeluhkan kebisingan?',
                'option_a' => 'Memindahkan tamu ke kamar lain',
                'option_b' => 'Mengabaikan keluhan tamu',
                'option_c' => 'Meminta tamu untuk diam',
                'option_d' => 'Menutup telinga',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 3,
                'question_text' => 'Apa fungsi housekeeping dalam hotel?',
                'option_a' => 'Membersihkan dan merawat kamar tamu',
                'option_b' => 'Menerima tamu di lobby',
                'option_c' => 'Menyiapkan makanan di restoran',
                'option_d' => 'Mengatur keamanan hotel',
                'correct_option' => 'A'
            ]
        ];

        // Questions for BSN Schema (skema_id = 4)
        $bsnQuestions = [
            [
                'skema_id' => 4,
                'question_text' => 'Apa yang dimaksud dengan pattern dalam desain busana?',
                'option_a' => 'Pola dasar untuk membuat pakaian',
                'option_b' => 'Warna-warna yang digunakan',
                'option_c' => 'Bahan yang dipilih',
                'option_d' => 'Model yang dipakai',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 4,
                'question_text' => 'Apa fungsi dari seam allowance dalam menjahit?',
                'option_a' => 'Jahitan tambahan untuk kelonggaran',
                'option_b' => 'Hiasan pada pakaian',
                'option_c' => 'Tali pengikat pakaian',
                'option_d' => 'Kancing pakaian',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 4,
                'question_text' => 'Apa yang harus diperhatikan saat memilih bahan untuk pakaian?',
                'option_a' => 'Jenis kain, warna, dan kualitas',
                'option_b' => 'Harga yang murah',
                'option_c' => 'Merek yang terkenal',
                'option_d' => 'Kemasan yang menarik',
                'correct_option' => 'A'
            ]
        ];

        // Questions for ULW Schema (skema_id = 5)
        $ulwQuestions = [
            [
                'skema_id' => 5,
                'question_text' => 'Apa yang dimaksud dengan tour guide?',
                'option_a' => 'Pemandu wisata yang menjelaskan destinasi',
                'option_b' => 'Pengemudi bus wisata',
                'option_c' => 'Penjual tiket wisata',
                'option_d' => 'Petugas keamanan wisata',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 5,
                'question_text' => 'Apa yang harus dilakukan saat wisatawan mengalami kecelakaan?',
                'option_a' => 'Memberikan pertolongan pertama dan menghubungi medis',
                'option_b' => 'Mengabaikan kecelakaan',
                'option_c' => 'Menyuruh wisatawan untuk pulang',
                'option_d' => 'Melanjutkan perjalanan wisata',
                'correct_option' => 'A'
            ],
            [
                'skema_id' => 5,
                'question_text' => 'Apa fungsi dari itinerary dalam perjalanan wisata?',
                'option_a' => 'Jadwal perjalanan wisata yang terencana',
                'option_b' => 'Daftar harga tiket wisata',
                'option_c' => 'Daftar hotel yang tersedia',
                'option_d' => 'Daftar restoran yang direkomendasikan',
                'correct_option' => 'A'
            ]
        ];

        // Create all questions
        foreach ($rplQuestions as $question) {
            Question::create($question);
        }

        foreach ($tbgQuestions as $question) {
            Question::create($question);
        }

        foreach ($phQuestions as $question) {
            Question::create($question);
        }

        foreach ($bsnQuestions as $question) {
            Question::create($question);
        }

        foreach ($ulwQuestions as $question) {
            Question::create($question);
        }
    }
}
