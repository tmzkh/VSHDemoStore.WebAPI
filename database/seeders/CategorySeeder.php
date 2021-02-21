<?php

namespace Database\Seeders;

use App\Models\Taxon;
use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $baseCategory = Taxonomy::create([
            'name' => 'Clothes'
        ]);

        $subCategories = $this->seedFirstLevelCategories($baseCategory);

        foreach ($subCategories as $subCategory) {
            $this->seedSecondLevelCategories($baseCategory, $subCategory);
        }
    }

    /**
     * Seed subcategories (men, women, unisex) for base category (e.g. clothes)
     *
     * @param Taxonomy $category
     * @return array
     */
    private function seedFirstLevelCategories(Taxonomy $category): array
    {
        $subCategories = [];

        foreach (['men', 'women', 'unisex'] as $subcategoryName) {
            $subCategories[] = Taxon::create([
                'name' => $subcategoryName,
                'taxonomy_id' => $category->id
            ]);
        }

        return $subCategories;
    }

    /**
     * Seed subcategories (e.g. clothes type) for first level subcategories (e.g. 'men' or 'women').
     *
     * @param Taxonomy $baseCategory
     * @param Taxon $category
     * @return array
     */
    private function seedSecondLevelCategories(Taxonomy $baseCategory, Taxon $category): array
    {
        $subCategories = [];

        foreach (['T-shirts', 'Trousers', 'Shoes'] as $subcategoryName) {
            $subCategories[] = Taxon::create([
                'name' => $subcategoryName,
                'taxonomy_id' => $baseCategory->id,
                'parent_id' => $category->id
            ]);
        }

        return $subCategories;
    }
}
