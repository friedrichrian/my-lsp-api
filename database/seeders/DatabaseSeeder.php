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
            EnsureSchemaUnitsSeeder::class,
            DemoActiveAssessmentSeeder::class, // Added for demo
            DemoMultipleAssessmentSeeder::class, // Multiple assessments for testing
            CleanupAsesiAssessmentsSeeder::class, // Clean up duplicate assessments
            CreateApl01ForAsesiSeeder::class, // Create APL-01 for test asesi
        ]);
    }
}
