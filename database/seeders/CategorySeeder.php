<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'GPU (Graphics Cards)',
                'slug' => 'gpu',
                'icon' => 'cpu',
                'description' => 'Graphics processing units and video cards',
                'subcategories' => [
                    'NVIDIA GeForce',
                    'AMD Radeon',
                    'Workstation GPU',
                    'Mining GPU'
                ]
            ],
            [
                'name' => 'CPU (Processors)',
                'slug' => 'cpu',
                'icon' => 'cpu',
                'description' => 'Central processing units for gaming and servers',
                'subcategories' => [
                    'Intel Core',
                    'AMD Ryzen',
                    'Intel Xeon (Server)',
                    'AMD Threadripper'
                ]
            ],
            [
                'name' => 'Motherboard',
                'slug' => 'motherboard',
                'icon' => 'layers',
                'description' => 'System boards and server boards',
                'subcategories' => [
                    'Intel Motherboards',
                    'AMD Motherboards',
                    'Server Motherboards'
                ]
            ],
            [
                'name' => 'RAM (Memory)',
                'slug' => 'ram',
                'icon' => 'database',
                'description' => 'DDR3, DDR4, DDR5 and server RAM modules',
                'subcategories' => [
                    'DDR4 RAM',
                    'DDR5 RAM',
                    'DDR3 RAM',
                    'Server ECC RAM'
                ]
            ],
            [
                'name' => 'Storage',
                'slug' => 'storage',
                'icon' => 'hard-drive',
                'description' => 'Solid state drives, hard drives and external storage',
                'subcategories' => [
                    'SSD (SATA)',
                    'NVMe SSD (M.2)',
                    'HDD (Hard Disk)',
                    'External Storage'
                ]
            ],
            [
                'name' => 'PSU (Power Supply)',
                'slug' => 'psu',
                'icon' => 'zap',
                'description' => 'Modular and non-modular power supplies',
                'subcategories' => [
                    'Modular PSU',
                    'Semi-Modular PSU',
                    'Non-Modular PSU'
                ]
            ],
            [
                'name' => 'Cabinet (PC Case)',
                'slug' => 'cabinet',
                'icon' => 'box',
                'description' => 'Computer towers and server racks',
                'subcategories' => [
                    'Full Tower',
                    'Mid Tower',
                    'Mini ITX',
                    'Server Rack Case'
                ]
            ],
            [
                'name' => 'Cooling',
                'slug' => 'cooling',
                'icon' => 'wind',
                'description' => 'CPU air coolers, liquid AIOs, case fans',
                'subcategories' => [
                    'Air Cooler',
                    'AIO Liquid Cooler',
                    'Custom Water Cooling',
                    'Case Fans'
                ]
            ],
            [
                'name' => 'Peripheral',
                'slug' => 'peripheral',
                'icon' => 'keyboard',
                'description' => 'Keyboards, mice, monitors and other accessories',
                'subcategories' => [
                    'Keyboard',
                    'Mouse',
                    'Monitor',
                    'Headset',
                    'Webcam',
                    'Mousepad'
                ]
            ],
            [
                'name' => 'Networking',
                'slug' => 'networking',
                'icon' => 'wifi',
                'description' => 'Routers, switches, LAN cards and WiFi adapters',
                'subcategories' => [
                    'WiFi Card',
                    'Network Card (LAN)',
                    'Router (Used)',
                    'Network Switch'
                ]
            ],
            [
                'name' => 'Cables & Accessories',
                'slug' => 'cables',
                'icon' => 'link',
                'description' => 'PCIe, SATA, power and display connection cables',
                'subcategories' => [
                    'SATA Cables',
                    'PCIe Cables',
                    'Display Cables',
                    'Power Cables'
                ]
            ],
            [
                'name' => 'Full PC Builds',
                'slug' => 'full_build',
                'icon' => 'monitor',
                'description' => 'Pre-built gaming systems and server workstation systems',
                'subcategories' => [
                    'Gaming PC',
                    'Workstation PC',
                    'Budget PC Build',
                    'Server System'
                ]
            ]
        ];

        // Seed parent categories & subcategories
        foreach ($categoriesData as $index => $cData) {
            $parent = Category::updateOrCreate(
                ['slug' => $cData['slug']],
                [
                    'name' => $cData['name'],
                    'icon' => $cData['icon'],
                    'description' => $cData['description'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            foreach ($cData['subcategories'] as $subIndex => $subName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($subName)],
                    [
                        'name' => $subName,
                        'parent_id' => $parent->id,
                        'icon' => $parent->icon,
                        'is_active' => true,
                        'sort_order' => $subIndex,
                    ]
                );
            }
        }

        // Map existing listings to categories
        $listings = Listing::all();
        foreach ($listings as $listing) {
            $oldCat = $listing->attributes['category'] ?? null;
            if ($oldCat) {
                $parentCategory = Category::where('slug', $oldCat)->first();
                if ($parentCategory) {
                    $listing->category_id = $parentCategory->id;
                    $sub = $parentCategory->children()->first();
                    if ($sub) {
                        $listing->subcategory_id = $sub->id;
                    }
                    $listing->save();
                }
            }
        }

        // Recalculate listing counts
        foreach (Category::all() as $cat) {
            $count = Listing::where(function($q) use ($cat) {
                $q->where('category_id', $cat->id)
                  ->orWhere('subcategory_id', $cat->id);
            })
            ->where('listing_status', 'active')
            ->count();
            
            $cat->update(['listing_count' => $count]);
        }
    }
}
