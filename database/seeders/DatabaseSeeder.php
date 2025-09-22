<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            JurusanSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            AssesiSeeder::class,
            AssesorSeeder::class,
            SchemaSeeder::class,
            KomponenSeeder::class,
            AssesmentSeeder::class,
            QuestionSeeder::class,
            FormApl01Seeder::class,
            BuktiDokumenSeeder::class,
            BuktiDokumenAsesiRpl1Seeder::class,
            AssesmentAsesiSeeder::class,
            IaDocSeeder::class,
            DemoRpl1AssesiSeeder::class,
        ]);
    }
}
