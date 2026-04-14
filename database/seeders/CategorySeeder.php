<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Action',
                'icon' => 'fa-fist-raised',
                'description' => 'High-energy stories with combat, chases, and intense sequences.',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Drama',
                'icon' => 'fa-theater-masks',
                'description' => 'Emotional stories focusing on character development and relationships.',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Comedy',
                'icon' => 'fa-laugh-squint',
                'description' => 'Humorous stories with witty dialogue and funny situations.',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Sci-Fi',
                'icon' => 'fa-rocket',
                'description' => 'Futuristic stories with advanced technology and space exploration.',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Fantasy',
                'icon' => 'fa-dragon',
                'description' => 'Magical stories with mythical creatures and enchanted worlds.',
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Horror',
                'icon' => 'fa-ghost',
                'description' => 'Terrifying stories with suspense, fear, and supernatural elements.',
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Romance',
                'icon' => 'fa-heart',
                'description' => 'Love stories focusing on relationships and emotional connections.',
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Thriller',
                'icon' => 'fa-skull',
                'description' => 'Suspenseful stories with unexpected twists and gripping plots.',
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Mystery',
                'icon' => 'fa-search',
                'description' => 'Puzzle stories with clues, investigations, and hidden secrets.',
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Adventure',
                'icon' => 'fa-map-marked-alt',
                'description' => 'Exciting journeys with exploration, discovery, and epic quests.',
                'display_order' => 10,
                'is_active' => true,
            ],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}