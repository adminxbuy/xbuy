<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'SHOP',
            'SELL',
            'SUPPORT',
            'COMPANY & SERVICES'
        ];

        $categoryModels = [];
        foreach ($categories as $catName) {
            $categoryModels[$catName] = PageCategory::updateOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName]
            );
        }

        // Map existing pages
        $mappings = [
            'privacy-policy' => 'COMPANY & SERVICES',
            'terms-of-service' => 'COMPANY & SERVICES',
            'about-us' => 'COMPANY & SERVICES',
            'contact-us' => 'SUPPORT',
            'escrow-policy' => 'SUPPORT',
            'seller-guidelines' => 'SELL',
            'buyer-protection' => 'SHOP',
            'refund-policy' => 'SUPPORT',
            'faqs' => 'SUPPORT',
            'careers' => 'COMPANY & SERVICES',
        ];

        foreach ($mappings as $slug => $catName) {
            $page = Page::where('slug', $slug)->first();
            if ($page && isset($categoryModels[$catName])) {
                $page->update(['category_id' => $categoryModels[$catName]->id]);
            }
        }
    }
}
