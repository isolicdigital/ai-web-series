<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComedyCategory;
use App\Models\ComedyTemplate;

class ComedySeeder extends Seeder
{
    public function run(): void
    {
        ComedyTemplate::query()->delete();
        ComedyCategory::query()->delete();
        $categories = [
            ['name' => 'Stand-Up Routines', 'slug' => 'stand-up-routines', 'icon' => 'fas fa-microphone-alt', 'description' => 'Classic stand-up comedy bits and routines', 'display_order' => 1],
            ['name' => 'Social Media Comedy', 'slug' => 'social-media-comedy', 'icon' => 'fas fa-share-alt', 'description' => 'Short viral-friendly comedy sketches', 'display_order' => 2],
            ['name' => 'Funny Podcast', 'slug' => 'funny-podcast', 'icon' => 'fas fa-podcast', 'description' => 'Conversational comedy and banter', 'display_order' => 3],
            ['name' => 'Character Comedy Videos', 'slug' => 'character-comedy', 'icon' => 'fas fa-theater-masks', 'description' => 'Unique characters and impersonations', 'display_order' => 4],
            ['name' => 'Magical Comedy Show', 'slug' => 'magical-comedy', 'icon' => 'fas fa-magic', 'description' => 'Comedy meets illusion and magic', 'display_order' => 5],
            ['name' => 'My Videos', 'slug' => 'my-videos', 'icon' => 'fas fa-video', 'description' => 'Your created comedy videos', 'display_order' => 6],
        ];

        foreach ($categories as $cat) {
            ComedyCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $standUpCategory = ComedyCategory::where('slug', 'stand-up-routines')->first();

        $templates = [
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Black Box Theater',
                'description' => 'Bold and expressive comedian with commanding stage presence',
                'init_image' => '/public/templates/standup/african-female-black-box-theater.jpg',
                'reference_image' => '/public/templates/standup/african-female-black-box-theater-face.png',
                'preview_image' => '/public/templates/standup/african-female-black-box-theater.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Neon Comedy Lounge',
                'description' => 'Charismatic storyteller with smooth delivery and infectious energy',
                'init_image' => '/public/templates/standup/african-male-neon-comedy-lounge.jpg',
                'reference_image' => '/public/templates/standup/african-male-neon-comedy-lounge-face.png',
                'preview_image' => '/public/templates/standup/african-male-neon-comedy-lounge.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Rooftop Comedy Night',
                'description' => 'Edgy and quick-witted comedian with sharp punchlines',
                'init_image' => '/public/templates/standup/caucasian-female-rooftop-comedy-night.jpg',
                'reference_image' => '/public/templates/standup/caucasian-female-rooftop-comedy-night-face.png',
                'preview_image' => '/public/templates/standup/caucasian-female-rooftop-comedy-night.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Large Theater Stage',
                'description' => 'Bombastic and energetic performer with physical comedy style',
                'init_image' => '/public/templates/standup/caucasian-male-large-theater-stage.jpg',
                'reference_image' => '/public/templates/standup/caucasian-male-large-theater-stage-face.png',
                'preview_image' => '/public/templates/standup/caucasian-male-large-theater-stage.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Retro Comedy Club',
                'description' => 'Deadpan delivery with clever social commentary',
                'init_image' => '/public/templates/standup/east-asian-female-retro-comedy-club.jpg',
                'reference_image' => '/public/templates/standup/east-asian-female-retro-comedy-club-face.png',
                'preview_image' => '/public/templates/standup/east-asian-female-retro-comedy-club.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Minimalist Studio Stage',
                'description' => 'Calm and cerebral comedian with precise timing',
                'init_image' => '/public/templates/standup/east-asian-male-minimalist-studio-stage.jpg',
                'reference_image' => '/public/templates/standup/east-asian-male-minimalist-studio-stage-face.png',
                'preview_image' => '/public/templates/standup/east-asian-male-minimalist-studio-stage.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Open Mic Cafe',
                'description' => 'Warm and relatable comedian with everyday humor',
                'init_image' => '/public/templates/standup/indian-female-open-mic-cafe.jpg',
                'reference_image' => '/public/templates/standup/indian-female-open-mic-cafe-face.png',
                'preview_image' => '/public/templates/standup/indian-female-open-mic-cafe.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Basement Comedy Club',
                'description' => 'Story-driven comedian with rich cultural perspective',
                'init_image' => '/public/templates/standup/indian-male-basement-comedy-club.jpg',
                'reference_image' => '/public/templates/standup/indian-male-basement-comedy-club-face.png',
                'preview_image' => '/public/templates/standup/indian-male-basement-comedy-club.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Bar Comedy Stage',
                'description' => 'Passionate and animated performer with high energy',
                'init_image' => '/public/templates/standup/latino-male-bar-comedy-stage.jpg',
                'reference_image' => '/public/templates/standup/latino-male-bar-comedy-stage-face.png',
                'preview_image' => '/public/templates/standup/latino-male-bar-comedy-stage.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => $standUpCategory->id,
                'name' => 'Outdoor Night Comedy',
                'description' => 'Satirical and clever comedian with sharp political wit',
                'init_image' => '/public/templates/standup/middle-eastern-male-outdoor-night-comedy.jpg',
                'reference_image' => '/public/templates/standup/middle-eastern-male-outdoor-night-comedy-face.png',
                'preview_image' => '/public/templates/standup/middle-eastern-male-outdoor-night-comedy.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            ComedyTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}