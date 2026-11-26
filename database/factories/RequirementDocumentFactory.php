<?php

namespace Database\Factories;

use App\Enums\RequirementDocumentType;
use App\Models\GraduationProcess;
use App\Models\RequirementDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RequirementDocument>
 */
class RequirementDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_process_id' => GraduationProcess::factory(),
            'type' => RequirementDocumentType::EnrollmentSlip,
            'file_path' => 'tramites/requisitos/'.Str::uuid().'.pdf',
            'original_file_name' => fake()->word().'.pdf',
            'file_size_bytes' => fake()->numberBetween(10_000, 2_000_000),
            'uploaded_by' => User::factory()->administrativeAssistant(),
            'uploaded_at' => now(),
        ];
    }
}
