<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GroupCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Define top-level categories and their subcategories
        $data = [
            [
                'name' => 'Mental & Emotional Health',
                'description' => 'Peer and family support for mental and emotional wellbeing.',
                'position' => 1,
                'subcategories' => [
                    'Depression & Anxiety',
                    'PTSD & Trauma Recovery',
                    'Bipolar Disorder',
                    'Eating Disorders',
                    'Obsessive-Compulsive Disorder (OCD)',
                    'Phobias & Panic',
                    'Grief & Bereavement',
                    'Suicide Prevention & Crisis Support',
                ],
            ],
            [
                'name' => 'Chronic Illness & Autoimmune Conditions',
                'description' => 'Support for ongoing and immune-related conditions.',
                'position' => 2,
                'subcategories' => [
                    'Diabetes (Type 1 & Type 2)',
                    'Hypertension & Heart Disease',
                    'Asthma & COPD',
                    'Lupus (SLE)',
                    'Rheumatoid Arthritis',
                    'Multiple Sclerosis (MS)',
                    'Fibromyalgia & Chronic Pain',
                    'Inflammatory Bowel Disease (Crohn’s & Ulcerative Colitis)',
                    'Chronic Kidney Disease',
                ],
            ],
            [
                'name' => 'Cancer Support',
                'description' => 'Support for patients, survivors, and caregivers across cancer journeys.',
                'position' => 3,
                'subcategories' => [
                    'Breast Cancer',
                    'Prostate Cancer',
                    'Leukemia & Lymphoma',
                    'Colorectal Cancer',
                    'Lung Cancer',
                    'Gynecologic Cancers',
                    'Childhood Cancer (Parents & Families)',
                    'Survivors & Remission',
                    'Palliative Oncology',
                ],
            ],
            [
                'name' => 'Women’s & Family Health',
                'description' => 'Support around fertility, pregnancy, parenting, and menopause.',
                'position' => 4,
                'subcategories' => [
                    'Fertility & IVF',
                    'Pregnancy Support',
                    'High-Risk Pregnancy',
                    'Postpartum & New Mothers',
                    'Perinatal Mood & Anxiety Disorders',
                    'Parenting Special Needs Children',
                    'Menopause & Perimenopause',
                    'Endometriosis & PCOS',
                ],
            ],
            [
                'name' => 'Children & Youth Support',
                'description' => 'Groups focused on pediatric and adolescent needs.',
                'position' => 5,
                'subcategories' => [
                    'Autism Spectrum Disorder (ASD)',
                    'ADHD',
                    'Learning Disabilities',
                    'Rare Pediatric Conditions',
                    'Teen Chronic Illness',
                    'Pediatric Mental Health',
                ],
            ],
            [
                'name' => 'Elderly & Caregiving',
                'description' => 'Support for older adults and those who care for them.',
                'position' => 6,
                'subcategories' => [
                    'Alzheimer’s & Dementia Caregivers',
                    'Parkinson’s Disease',
                    'Stroke Recovery',
                    'General Caregiver Support',
                    'End-of-Life & Hospice Care Families',
                    'Fall Prevention & Mobility',
                ],
            ],
            [
                'name' => 'Addiction & Recovery',
                'description' => 'Recovery communities for substance and behavioral addictions.',
                'position' => 7,
                'subcategories' => [
                    'Alcohol Recovery',
                    'Drug/Substance Use Recovery',
                    'Opioid Use Disorder',
                    'Smoking & Vaping Cessation',
                    'Gambling Addiction',
                    'Technology & Screen Addiction',
                    'Family & Friends of People in Recovery',
                ],
            ],
            [
                'name' => 'Disability & Accessibility',
                'description' => 'Peer support for disability, adaptation, and advocacy.',
                'position' => 8,
                'subcategories' => [
                    'Physical Disability',
                    'Vision Impairment',
                    'Hearing Impairment',
                    'Spinal Cord Injury',
                    'Amputee & Prosthetics Users',
                    'Independent Living & Advocacy',
                    'Assistive Technology Users',
                ],
            ],
            [
                'name' => 'Pain, Rehab & Recovery',
                'description' => 'Rehabilitation, pain management, and recovery communities.',
                'position' => 9,
                'subcategories' => [
                    'Post-Surgical Recovery',
                    'Chronic Pain Management',
                    'Back & Spine Conditions',
                    'Joint & Orthopedic Rehab',
                    'Physical Therapy & Occupational Therapy',
                ],
            ],
            [
                'name' => 'Rare & Undiagnosed Conditions',
                'description' => 'Support for people with rare diseases or ongoing diagnostic journeys.',
                'position' => 10,
                'subcategories' => [
                    'Rare Disease General Support',
                    'Genetic & Hereditary Disorders',
                    'Undiagnosed/Diagnostic Odyssey',
                ],
            ],
        ];

        // Upsert categories then subcategories
        foreach ($data as $catIndex => $cat) {
            $categorySlug = Str::slug($cat['name']);

            DB::table('group_categories')->upsert([
                'name' => $cat['name'],
                'slug' => $categorySlug,
                'description' => $cat['description'] ?? null,
                'position' => $cat['position'] ?? ($catIndex + 1),
                'created_at' => $now,
                'updated_at' => $now,
            ], ['slug'], ['name', 'description', 'position', 'updated_at']);

            // Get category id
            $categoryId = DB::table('group_categories')->where('slug', $categorySlug)->value('id');

            // Seed subcategories
            foreach ($cat['subcategories'] as $subIndex => $subName) {
                $subSlug = Str::slug($subName);

                DB::table('group_sub_categories')->upsert([
                    'category_id' => $categoryId,
                    'name' => $subName,
                    'slug' => $subSlug,
                    'position' => $subIndex + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], ['slug'], ['category_id', 'name', 'position', 'updated_at']);
            }
        }
    }
}
