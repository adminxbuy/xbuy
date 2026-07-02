<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandsByCategory = [
            'gpu' => [
                'NVIDIA', 'AMD', 'ASUS ROG', 'ASUS TUF', 'MSI Gaming', 
                'MSI Mech', 'Gigabyte Gaming', 'Gigabyte Eagle', 
                'Zotac Gaming', 'Zotac Twin Edge', 'Sapphire Nitro', 
                'PowerColor', 'XFX', 'Palit', 'Inno3D'
            ],
            'cpu' => [
                'Intel', 'AMD'
            ],
            'motherboard' => [
                'ASUS', 'MSI', 'Gigabyte', 'ASRock', 'EVGA', 'Biostar'
            ],
            'ram' => [
                'Corsair', 'G.Skill', 'Kingston', 'Samsung', 'Crucial', 
                'HyperX', 'TeamGroup', 'Patriot'
            ],
            'storage' => [
                'Samsung', 'WD', 'Seagate', 'Crucial', 'Kingston', 
                'Sandisk', 'Toshiba', 'Sabrent', 'Silicon Power'
            ],
            'psu' => [
                'Corsair', 'EVGA', 'Seasonic', 'be quiet!', 'Cooler Master', 
                'Antec', 'Thermaltake', 'Fractal Design'
            ],
            'cabinet' => [
                'NZXT', 'Lian Li', 'Fractal Design', 'Corsair', 'Cooler Master', 
                'Phanteks', 'Thermaltake', 'be quiet!', 'Antec', 'SilverStone', 
                'DeepCool'
            ],
            'cooling' => [
                'Noctua', 'be quiet!', 'Cooler Master', 'DeepCool', 'NZXT', 
                'Corsair', 'Arctic', 'Thermalright', 'ID-Cooling', 'Scythe'
            ],
            'peripheral' => [
                'Logitech', 'Razer', 'SteelSeries', 'Corsair', 'HyperX', 
                'ASUS ROG', 'MSI', 'BenQ', 'LG', 'Samsung', 'Dell', 'AOC', 
                'ViewSonic'
            ],
        ];

        foreach ($brandsByCategory as $catSlug => $brandNames) {
            $category = Category::where('slug', $catSlug)->first();
            if (!$category) {
                continue;
            }

            foreach ($brandNames as $index => $name) {
                $slug = Str::slug($name);
                
                // Find or create the brand
                $brand = Brand::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );

                // Associate brand with this category
                $brand->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }
}
