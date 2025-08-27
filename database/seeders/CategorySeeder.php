<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Anelli',
            'Bracciali', 
            'Collane',
            'Orecchini',
            'Catene',
            'Ciondoli',
            'Orologi',
            'Spille',
            'Monete',
            'Lingotti',
            'Oggetti d\'arte',
            'Posate',
            'Altro oro',
            'Argento',
            'Platino',
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
            ]);
        }
    }
}
