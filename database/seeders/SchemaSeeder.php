<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schema;
use App\Models\Unit;
use App\Models\Element;
use App\Models\KriteriaUntukKerja;

class SchemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Schema untuk RPL
        $rplSchema = Schema::create([
            'jurusan_id' => 1, // RPL
            'judul_skema' => 'Pemrogram Junior',
            'nomor_skema' => 'SKM.001.01.01.01'
        ]);

        // Unit 1 untuk RPL
        $unit1 = Unit::create([
            'schema_id' => $rplSchema->id,
            'unit_ke' => 1,
            'kode_unit' => 'J.001.00.00.00',
            'judul_unit' => 'Menggunakan Struktur Data'
        ]);

        // Elemen untuk Unit 1
        $element1_1 = Element::create([
            'unit_id' => $unit1->id,
            'elemen_index' => 1,
            'nama_elemen' => 'Mengidentifikasi struktur data yang sesuai'
        ]);

        // KUK untuk Elemen 1.1
        KriteriaUntukKerja::create([
            'element_id' => $element1_1->id,
            'urutan' => '1.1',
            'deskripsi_kuk' => 'Mengidentifikasi jenis-jenis struktur data (array, list, stack, queue, tree, graph)'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element1_1->id,
            'urutan' => '1.2',
            'deskripsi_kuk' => 'Memilih struktur data yang sesuai berdasarkan kebutuhan aplikasi'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element1_1->id,
            'urutan' => '1.3',
            'deskripsi_kuk' => 'Menganalisis kompleksitas waktu dan ruang dari struktur data'
        ]);

        $element1_2 = Element::create([
            'unit_id' => $unit1->id,
            'elemen_index' => 2,
            'nama_elemen' => 'Mengimplementasikan struktur data'
        ]);

        // KUK untuk Elemen 1.2
        KriteriaUntukKerja::create([
            'element_id' => $element1_2->id,
            'urutan' => '2.1',
            'deskripsi_kuk' => 'Membuat implementasi struktur data menggunakan bahasa pemrograman'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element1_2->id,
            'urutan' => '2.2',
            'deskripsi_kuk' => 'Menguji implementasi struktur data dengan berbagai skenario'
        ]);

        // Schema untuk TBG
        $tbgSchema = Schema::create([
            'jurusan_id' => 2, // TBG
            'judul_skema' => 'Koki Junior',
            'nomor_skema' => 'SKM.002.01.01.01'
        ]);

        $unit2 = Unit::create([
            'schema_id' => $tbgSchema->id,
            'unit_ke' => 1,
            'kode_unit' => 'J.002.00.00.00',
            'judul_unit' => 'Mengolah Makanan Indonesia'
        ]);

        $element2_1 = Element::create([
            'unit_id' => $unit2->id,
            'elemen_index' => 1,
            'nama_elemen' => 'Menyiapkan bahan makanan'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element2_1->id,
            'urutan' => '1.1',
            'deskripsi_kuk' => 'Memilih bahan makanan yang segar dan berkualitas'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element2_1->id,
            'urutan' => '1.2',
            'deskripsi_kuk' => 'Mencuci dan memotong bahan makanan sesuai standar'
        ]);

        // Schema untuk PH
        $phSchema = Schema::create([
            'jurusan_id' => 3, // PH
            'judul_skema' => 'Front Office Hotel',
            'nomor_skema' => 'SKM.003.01.01.01'
        ]);

        $unit3 = Unit::create([
            'schema_id' => $phSchema->id,
            'unit_ke' => 1,
            'kode_unit' => 'J.003.00.00.00',
            'judul_unit' => 'Melayani Tamu Hotel'
        ]);

        $element3_1 = Element::create([
            'unit_id' => $unit3->id,
            'elemen_index' => 1,
            'nama_elemen' => 'Melakukan check-in tamu'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element3_1->id,
            'urutan' => '1.1',
            'deskripsi_kuk' => 'Memverifikasi identitas dan reservasi tamu'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element3_1->id,
            'urutan' => '1.2',
            'deskripsi_kuk' => 'Mengisi form registrasi tamu dengan lengkap'
        ]);

        // Schema untuk BSN
        $bsnSchema = Schema::create([
            'jurusan_id' => 4, // BSN
            'judul_skema' => 'Desainer Busana',
            'nomor_skema' => 'SKM.004.01.01.01'
        ]);

        $unit4 = Unit::create([
            'schema_id' => $bsnSchema->id,
            'unit_ke' => 1,
            'kode_unit' => 'J.004.00.00.00',
            'judul_unit' => 'Membuat Pola Busana'
        ]);

        $element4_1 = Element::create([
            'unit_id' => $unit4->id,
            'elemen_index' => 1,
            'nama_elemen' => 'Menggambar pola dasar'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element4_1->id,
            'urutan' => '1.1',
            'deskripsi_kuk' => 'Mengukur tubuh model dengan akurat'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element4_1->id,
            'urutan' => '1.2',
            'deskripsi_kuk' => 'Membuat pola dasar sesuai ukuran tubuh'
        ]);

        // Schema untuk ULW
        $ulwSchema = Schema::create([
            'jurusan_id' => 5, // ULW
            'judul_skema' => 'Pemandu Wisata',
            'nomor_skema' => 'SKM.005.01.01.01'
        ]);

        $unit5 = Unit::create([
            'schema_id' => $ulwSchema->id,
            'unit_ke' => 1,
            'kode_unit' => 'J.005.00.00.00',
            'judul_unit' => 'Memandu Wisatawan'
        ]);

        $element5_1 = Element::create([
            'unit_id' => $unit5->id,
            'elemen_index' => 1,
            'nama_elemen' => 'Menyampaikan informasi wisata'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element5_1->id,
            'urutan' => '1.1',
            'deskripsi_kuk' => 'Menjelaskan sejarah dan budaya destinasi wisata'
        ]);

        KriteriaUntukKerja::create([
            'element_id' => $element5_1->id,
            'urutan' => '1.2',
            'deskripsi_kuk' => 'Memberikan informasi praktis tentang destinasi wisata'
        ]);
    }
}