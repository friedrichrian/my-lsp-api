<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schema;
use App\Models\Unit;
use App\Models\Element;
use App\Models\KriteriaUntukKerja;

class FrIa01Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat jurusan terlebih dahulu jika belum ada
        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'TI'],
            [
                'nama_jurusan' => 'Teknologi Informasi',
                'jenjang' => 'S1',
                'deskripsi' => 'Program Studi Teknologi Informasi'
            ]
        );

        // Buat skema sertifikasi Pemrograman Junior
        $schema = Schema::create([
            'judul_skema' => 'Pemrograman Junior',
            'nomor_skema' => 'SSP.BNSP.001.2023',
            'jurusan_id' => $jurusan->id,
        ]);

        // Unit Kompetensi berdasarkan dokumen MUK
        $units = [
            [
                'unit_ke' => 1,
                'kode_unit' => 'J.610000.001.01',
                'judul_unit' => 'Menggunakan Algoritma Pemrograman Dasar',
                'elements' => [
                    [
                        'elemen_index' => 1,
                        'nama_elemen' => 'Mengidentifikasi kebutuhan algoritma',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Kebutuhan algoritma diidentifikasi sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Jenis algoritma ditentukan sesuai dengan kebutuhan program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Kompleksitas algoritma dianalisis sesuai dengan kebutuhan program']
                        ]
                    ],
                    [
                        'elemen_index' => 2,
                        'nama_elemen' => 'Mengembangkan algoritma',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Algoritma dikembangkan sesuai dengan kebutuhan program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Algoritma diuji sesuai dengan spesifikasi program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Algoritma didokumentasikan sesuai dengan standar dokumentasi']
                        ]
                    ]
                ]
            ],
            [
                'unit_ke' => 2,
                'kode_unit' => 'J.610000.002.01',
                'judul_unit' => 'Menggunakan Struktur Data Dasar',
                'elements' => [
                    [
                        'elemen_index' => 1,
                        'nama_elemen' => 'Mengidentifikasi struktur data',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Jenis struktur data diidentifikasi sesuai dengan kebutuhan program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Karakteristik struktur data dianalisis sesuai dengan kebutuhan program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Kompleksitas struktur data dianalisis sesuai dengan kebutuhan program']
                        ]
                    ],
                    [
                        'elemen_index' => 2,
                        'nama_elemen' => 'Mengimplementasikan struktur data',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Struktur data diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Operasi struktur data diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Struktur data diuji sesuai dengan spesifikasi program']
                        ]
                    ]
                ]
            ],
            [
                'unit_ke' => 3,
                'kode_unit' => 'J.610000.003.01',
                'judul_unit' => 'Menggunakan Pemrograman Berorientasi Objek',
                'elements' => [
                    [
                        'elemen_index' => 1,
                        'nama_elemen' => 'Mengidentifikasi konsep OOP',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Konsep OOP diidentifikasi sesuai dengan kebutuhan program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Prinsip OOP dianalisis sesuai dengan kebutuhan program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Pola desain OOP diidentifikasi sesuai dengan kebutuhan program']
                        ]
                    ],
                    [
                        'elemen_index' => 2,
                        'nama_elemen' => 'Mengimplementasikan OOP',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Kelas dan objek diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Inheritance diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Polymorphism diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 4, 'deskripsi_kuk' => 'Encapsulation diimplementasikan sesuai dengan spesifikasi program']
                        ]
                    ]
                ]
            ],
            [
                'unit_ke' => 4,
                'kode_unit' => 'J.610000.004.01',
                'judul_unit' => 'Menggunakan Database',
                'elements' => [
                    [
                        'elemen_index' => 1,
                        'nama_elemen' => 'Mengidentifikasi kebutuhan database',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Kebutuhan database diidentifikasi sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Jenis database ditentukan sesuai dengan kebutuhan program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Struktur database dianalisis sesuai dengan kebutuhan program']
                        ]
                    ],
                    [
                        'elemen_index' => 2,
                        'nama_elemen' => 'Mengimplementasikan database',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Database diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Query database diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Database diuji sesuai dengan spesifikasi program']
                        ]
                    ]
                ]
            ],
            [
                'unit_ke' => 5,
                'kode_unit' => 'J.610000.005.01',
                'judul_unit' => 'Menggunakan Web Development',
                'elements' => [
                    [
                        'elemen_index' => 1,
                        'nama_elemen' => 'Mengidentifikasi teknologi web',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Teknologi web diidentifikasi sesuai dengan kebutuhan program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Framework web ditentukan sesuai dengan kebutuhan program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Arsitektur web dianalisis sesuai dengan kebutuhan program']
                        ]
                    ],
                    [
                        'elemen_index' => 2,
                        'nama_elemen' => 'Mengimplementasikan web application',
                        'kriteria_untuk_kerja' => [
                            ['urutan' => 1, 'deskripsi_kuk' => 'Frontend diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 2, 'deskripsi_kuk' => 'Backend diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 3, 'deskripsi_kuk' => 'Database web diimplementasikan sesuai dengan spesifikasi program'],
                            ['urutan' => 4, 'deskripsi_kuk' => 'Web application diuji sesuai dengan spesifikasi program']
                        ]
                    ]
                ]
            ]
        ];

        // Insert units, elements, dan kriteria untuk kerja
        foreach ($units as $unitData) {
            $unit = Unit::create([
                'schema_id' => $schema->id,
                'unit_ke' => $unitData['unit_ke'],
                'kode_unit' => $unitData['kode_unit'],
                'judul_unit' => $unitData['judul_unit']
            ]);

            foreach ($unitData['elements'] as $elementData) {
                $element = Element::create([
                    'unit_id' => $unit->id,
                    'elemen_index' => $elementData['elemen_index'],
                    'nama_elemen' => $elementData['nama_elemen']
                ]);

                foreach ($elementData['kriteria_untuk_kerja'] as $kukData) {
                    KriteriaUntukKerja::create([
                        'element_id' => $element->id,
                        'urutan' => $kukData['urutan'],
                        'deskripsi_kuk' => $kukData['deskripsi_kuk']
                    ]);
                }
            }
        }

        $this->command->info('Data FR.IA.01 berhasil di-seed!');
    }
}
