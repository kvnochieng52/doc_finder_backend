<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicineCategory;
use App\Models\MedicineSubcategory;

class MedicineCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Medical Conditions',
                'description' => 'Medicines for treating various medical conditions and diseases',
                'icon' => 'medical-bag',
                'sort_order' => 1,
                'subcategories' => [
                    'Stomach Care',
                    'Pain Relief',
                    'Bone/Joint/Muscle Aches',
                    'Eye Care',
                    'Ear Care',
                    'Cough/Cold/Flu',
                    'Oral Care',
                    'First Aid',
                    'Nervous System Disorders',
                    'Diabetes',
                    'Reproductive Health',
                    'Hypertension',
                    'Endocrine Disorders',
                    'Allergy Relief',
                    'Bacterial/Fungal Infections',
                    'HIV',
                    'Heart Conditions',
                    'Kidney/Liver Health',
                    'Respiratory Disorders',
                    'Skin Conditions'
                ]
            ],
            [
                'name' => 'Vitamins and Supplements',
                'description' => 'Nutritional supplements, vitamins, and minerals for health maintenance',
                'icon' => 'pill',
                'sort_order' => 2,
                'subcategories' => [
                    'Multivitamins',
                    'Vitamin A',
                    'Vitamin B Complex',
                    'Vitamin C',
                    'Vitamin D',
                    'Vitamin E',
                    'Vitamin K',
                    'Calcium',
                    'Iron',
                    'Zinc',
                    'Magnesium',
                    'Omega-3',
                    'Probiotics',
                    'Protein Supplements',
                    'Herbal Supplements',
                    'Energy Boosters',
                    'Immune Support',
                    'Weight Management'
                ]
            ],
            [
                'name' => 'Personal Care',
                'description' => 'Personal hygiene and care products',
                'icon' => 'user',
                'sort_order' => 3,
                'subcategories' => [
                    'Oral Hygiene',
                    'Hair Care',
                    'Body Wash',
                    'Deodorants',
                    'Feminine Care',
                    'Baby Care',
                    'Elderly Care',
                    'Hand Sanitizers',
                    'Soap',
                    'Shampoo',
                    'Toothpaste',
                    'Mouthwash'
                ]
            ],
            [
                'name' => 'Beauty and Skin Care',
                'description' => 'Cosmetics, skincare, and beauty products',
                'icon' => 'heart',
                'sort_order' => 4,
                'subcategories' => [
                    'Face Care',
                    'Body Care',
                    'Anti-Aging',
                    'Acne Treatment',
                    'Moisturizers',
                    'Sunscreen',
                    'Cleansers',
                    'Toners',
                    'Serums',
                    'Masks',
                    'Lip Care',
                    'Eye Care',
                    'Men\'s Grooming'
                ]
            ],
            [
                'name' => 'Medical Devices',
                'description' => 'Medical equipment and devices for health monitoring and care',
                'icon' => 'stethoscope',
                'sort_order' => 5,
                'subcategories' => [
                    'Blood Pressure Monitors',
                    'Thermometers',
                    'Glucose Meters',
                    'Pulse Oximeters',
                    'Nebulizers',
                    'Weighing Scales',
                    'First Aid Kits',
                    'Bandages',
                    'Syringes',
                    'Masks',
                    'Gloves',
                    'Wheelchairs',
                    'Walking Aids',
                    'Hearing Aids'
                ]
            ],
            [
                'name' => 'Maternal and Child Health',
                'description' => 'Products for mothers, babies, and children',
                'icon' => 'baby',
                'sort_order' => 6,
                'subcategories' => [
                    'Prenatal Vitamins',
                    'Baby Formula',
                    'Baby Food',
                    'Diapers',
                    'Baby Bottles',
                    'Pacifiers',
                    'Baby Lotion',
                    'Baby Shampoo',
                    'Children\'s Vitamins',
                    'Teething Relief',
                    'Baby Safety',
                    'Maternity Care'
                ]
            ],
            [
                'name' => 'Chronic Disease Management',
                'description' => 'Medications and supplies for managing chronic conditions',
                'icon' => 'heart-pulse',
                'sort_order' => 7,
                'subcategories' => [
                    'Diabetes Management',
                    'Hypertension Control',
                    'Heart Disease',
                    'Kidney Disease',
                    'Liver Disease',
                    'Arthritis',
                    'Asthma/COPD',
                    'Mental Health',
                    'Epilepsy',
                    'Cancer Support',
                    'Autoimmune Disorders',
                    'Thyroid Disorders'
                ]
            ],
            [
                'name' => 'Emergency and First Aid',
                'description' => 'Emergency medications and first aid supplies',
                'icon' => 'first-aid',
                'sort_order' => 8,
                'subcategories' => [
                    'Emergency Medications',
                    'Wound Care',
                    'Burn Treatment',
                    'Antiseptics',
                    'Bandages',
                    'Pain Relief',
                    'Allergy Relief',
                    'Cold/Flu Relief',
                    'Digestive Relief',
                    'Eye Wash',
                    'Emergency Kits'
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $subcategoriesData = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            $category = MedicineCategory::create($categoryData);

            $sortOrder = 1;
            foreach ($subcategoriesData as $subcategoryName) {
                $slug = \Illuminate\Support\Str::slug($subcategoryName);
                
                // Check if slug exists and make it unique
                $counter = 1;
                $originalSlug = $slug;
                while (MedicineSubcategory::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                
                MedicineSubcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'slug' => $slug,
                    'description' => 'Subcategory for ' . $subcategoryName,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }
}
