<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            ['specialization_name' => 'General Practitioner', 'specialization_description' => 'General medical practitioner', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Cardiologist', 'specialization_description' => 'Heart and cardiovascular system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Dermatologist', 'specialization_description' => 'Skin, hair, and nail specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Pediatrician', 'specialization_description' => 'Child health specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Neurologist', 'specialization_description' => 'Nervous system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Orthopedic Surgeon', 'specialization_description' => 'Musculoskeletal system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Ophthalmologist', 'specialization_description' => 'Eye and vision specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'ENT Specialist', 'specialization_description' => 'Ear, nose, and throat specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Psychiatrist', 'specialization_description' => 'Mental health specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Gynecologist', 'specialization_description' => 'Female reproductive health specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Urologist', 'specialization_description' => 'Urinary tract and male reproductive system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Radiologist', 'specialization_description' => 'Medical imaging specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Anesthesiologist', 'specialization_description' => 'Anesthesia and pain management specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Emergency Medicine', 'specialization_description' => 'Emergency care specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Family Medicine', 'specialization_description' => 'Family health specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Oncologist', 'specialization_description' => 'Cancer specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Pulmonologist', 'specialization_description' => 'Lung and respiratory system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Gastroenterologist', 'specialization_description' => 'Digestive system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Endocrinologist', 'specialization_description' => 'Hormone and endocrine system specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
            ['specialization_name' => 'Rheumatologist', 'specialization_description' => 'Joints and autoimmune disease specialist', 'is_active' => 1, 'is_active_for_facility' => 1],
        ];

        DB::table('specializations')->insert($specializations);
    }
}
