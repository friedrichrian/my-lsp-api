<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assesment_Asesi;

class FixAsesiStatusSeeder extends Seeder
{
    public function run(): void
    {
        // Find the specific asesi
        $user = User::where('email', 'asesi.rpl1@student.smkn24.ac.id')->first();
        
        if (!$user || !$user->assesi) {
            $this->command->error('Asesi asesi.rpl1@student.smkn24.ac.id not found!');
            return;
        }

        $assesi = $user->assesi;
        $this->command->info("Found asesi: {$user->name} (ID: {$assesi->id})");

        // Get all assessments for this asesi
        $assessments = Assesment_Asesi::where('assesi_id', $assesi->id)->get();
        
        $this->command->info("Found {$assessments->count()} assessment(s) for this asesi:");
        
        foreach ($assessments as $assessment) {
            $this->command->info("- Assessment ID: {$assessment->assesment_id}, Status: {$assessment->status}");
        }

        // Update the first assessment to 'mengerjakan' status if it exists
        if ($assessments->count() > 0) {
            $firstAssessment = $assessments->first();
            $firstAssessment->update(['status' => 'mengerjakan']);
            $this->command->info("Updated first assessment (ID: {$firstAssessment->assesment_id}) to 'mengerjakan' status");
        }

        // If no assessments exist, the DemoMultipleAssessmentSeeder should have created them
        if ($assessments->count() === 0) {
            $this->command->warn('No assessments found for this asesi. Make sure DemoMultipleAssessmentSeeder ran successfully.');
        }

        $this->command->info('FixAsesiStatusSeeder completed!');
    }
}
