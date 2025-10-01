<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assesment_Asesi;
use Illuminate\Support\Facades\DB;

class CleanupAsesiAssessmentsSeeder extends Seeder
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

        // Get all existing assessments for this asesi
        $existingAssessments = Assesment_Asesi::where('assesi_id', $assesi->id)->get();
        
        $this->command->info("Found {$existingAssessments->count()} existing assessment(s) for this asesi");
        
        if ($existingAssessments->count() > 0) {
            // Keep only the first one, delete the rest
            $firstAssessment = $existingAssessments->first();
            $toDelete = $existingAssessments->slice(1);
            
            foreach ($toDelete as $assessment) {
                $this->command->info("Deleting duplicate assessment registration: Assessment ID {$assessment->assesment_id}");
                $assessment->delete();
            }
            
            // Update the remaining one to 'mengerjakan' status
            $firstAssessment->update(['status' => 'mengerjakan']);
            $this->command->info("Updated remaining assessment (ID: {$firstAssessment->assesment_id}) to 'mengerjakan' status");
        }

        $this->command->info('Cleanup completed! Asesi should now be able to access forms without "already registered" errors.');
    }
}
