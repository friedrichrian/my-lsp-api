<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schema;
use App\Models\Unit;
use App\Models\Element;
use App\Models\KriteriaUntukKerja;

class EnsureSchemaUnitsSeeder extends Seeder
{
    /**
     * Ensure each existing Schema has at least one Unit with minimal Elements/KUK
     */
    public function run(): void
    {
        $schemas = Schema::all();
        foreach ($schemas as $schema) {
            if ($schema->units()->count() > 0) {
                // Already has units; skip
                continue;
            }

            // Create 2 units for better testing
            for ($unitNum = 1; $unitNum <= 2; $unitNum++) {
                $unit = $schema->units()->create([
                    'unit_ke'    => $unitNum,
                    'kode_unit'  => 'J.' . str_pad((string)$schema->id, 3, '0', STR_PAD_LEFT) . '.0' . $unitNum . '.00.00',
                    'judul_unit' => 'Unit Kompetensi ' . $unitNum . ' - ' . $schema->judul_skema
                ]);

                // Create 2-3 elements per unit
                $elementsCount = $unitNum == 1 ? 3 : 2;
                for ($elIndex = 1; $elIndex <= $elementsCount; $elIndex++) {
                    $element = $unit->elements()->create([
                        'elemen_index' => $elIndex,
                        'nama_elemen'  => 'Elemen ' . $elIndex . ' Unit ' . $unitNum
                    ]);

                    // Create 2-3 KUK per element
                    $kukCount = rand(2, 3);
                    for ($kukIndex = 1; $kukIndex <= $kukCount; $kukIndex++) {
                        KriteriaUntukKerja::create([
                            'element_id'   => $element->id,
                            'urutan'       => $elIndex . '.' . $kukIndex,
                            'deskripsi_kuk'=> 'Kriteria Unjuk Kerja ' . $elIndex . '.' . $kukIndex . ' untuk ' . $element->nama_elemen
                        ]);
                    }
                }
            }

            $this->command?->info("EnsureSchemaUnitsSeeder: Created 2 units with elements for schema ID {$schema->id} ({$schema->judul_skema})");
        }
    }
}
