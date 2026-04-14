<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryTemplate;
use App\Models\Category;

class CategoryTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'category_name' => 'Action',
                'category_prompt' => [
                    'concept_generator' => 'Write a gripping episode concept for "{series_name}", an Action web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on high-stakes action, heroic moments, intense combat sequences, and thrilling chase scenes. Include the protagonist\'s motivation, the central conflict, a key action set piece, and a cliffhanger ending. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A dramatic action scene from "{scene_title}". {scene_description} The hero strikes a heroic pose, ready for battle. Explosions and sparks fill the background. Dynamic camera angle captures the intensity. Dramatic lighting with rim lights highlighting the hero. Fast-paced energy frozen in a single frame. Cinematic action movie style, high contrast lighting, dynamic composition. 8K resolution, professional stunt photography, film grain.'
                ],
                'init_image' => '/public/templates/categories_image/ai-action.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Drama',
                'category_prompt' => [
                    'concept_generator' => 'Write a powerful episode concept for "{series_name}", a Drama web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on emotional depth, character relationships, personal struggles, and meaningful moments of connection. Include the protagonist\'s internal conflict, a difficult choice they must face, a pivotal relationship moment, and an emotional climax. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'An emotional drama moment from "{scene_title}". {scene_description} The character\'s face reveals deep emotion through subtle expressions. Soft lighting creates an intimate atmosphere. The composition focuses on the connection between characters. Intimate dramatic style, soft natural lighting, shallow depth of field, warm earth tones. 8K resolution, emotional storytelling, filmic look.'
                ],
                'init_image' => '/public/templates/categories_image/ai-drama.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Comedy',
                'category_prompt' => [
                    'concept_generator' => 'Write a hilarious episode concept for "{series_name}", a Comedy web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on humorous situations, witty dialogue, comedic timing, and laugh-out-loud moments. Include the comedic premise, escalating absurdity, a funny set piece, and a satisfying punchline. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A funny comedy moment from "{scene_title}". {scene_description} The characters\' exaggerated expressions and body language sell the joke. Bright, colorful setting enhances the lighthearted mood. Medium shot captures the comedic timing perfectly. Bright comedy style, high-key lighting, vibrant cheerful colors, upbeat composition. 8K resolution, professional comedy timing, lively atmosphere.',
                    
                    'joke' => 'Generate a 2-line stand-up joke about {user_prompt}. STRICT LIMIT: 15 words total. Line 1: Setup. Line 2: Punchline. Output ONLY the 2 lines. No explanations, no prefixes, no meta text.'
                ],
                'init_image' => '/public/templates/categories_image/ai-comedy.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Sci-Fi',
                'category_prompt' => [
                    'concept_generator' => 'Write a mind-bending episode concept for "{series_name}", a Sci-Fi web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on futuristic technology, advanced concepts, world-building, and mind-bending revelations. Include the sci-fi hook, world-building details, a philosophical question or ethical dilemma, and a mind-bending twist. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A futuristic sci-fi scene from "{scene_title}". {scene_description} Advanced technology surrounds the characters. Neon lights reflect off sleek surfaces. Holographic displays float in the air. Cyberpunk aesthetic with atmospheric fog. Sci-fi style, neon lighting, futuristic colors, immersive world-building. 8K resolution, VFX quality, cyberpunk aesthetic.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Sci-Fi.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Fantasy',
                'category_prompt' => [
                    'concept_generator' => 'Write an epic episode concept for "{series_name}", a Fantasy web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on magical elements, mythical creatures, enchanted settings, and epic adventures. Include the fantasy elements, a mythical creature or enchanted setting, the hero\'s call to adventure, and a spellbinding cliffhanger. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A magical fantasy scene from "{scene_title}". {scene_description} Mythical creatures and magic glow in the air. Ancient ruins or enchanted forests provide the backdrop. Epic fantasy aesthetic with warm magical lighting. Fantasy style, magical lighting, enchanted colors, mystical atmosphere. 8K resolution, VFX quality, storybook aesthetic.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Fantasy.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Horror',
                'category_prompt' => [
                    'concept_generator' => 'Write a terrifying episode concept for "{series_name}", a Horror web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on suspense, fear, supernatural elements, psychological terror, and creeping dread. Include the horror premise, a suspenseful sequence, a shocking moment, and a lingering fear. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A terrifying horror scene from "{scene_title}". {scene_description} Dark shadows hide unknown threats. Creepy atmosphere with low-key lighting. The character shows genuine fear through their expression. Something evil lurks just out of sight. Horror style, low-key lighting, dark desaturated colors, unsettling composition. 8K resolution, atmospheric dread, tense framing.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Horror.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Romance',
                'category_prompt' => [
                    'concept_generator' => 'Write a heartwarming episode concept for "{series_name}", a Romance web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on emotional connection, relationship development, romantic moments, and heartfelt interactions. Include the romantic premise, emotional beats, an obstacle to overcome, and a swoon-worthy moment. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A romantic scene from "{scene_title}". {scene_description} Two people share an intimate connection. Soft lighting creates warm atmosphere. Their expressions show love and affection. The moment feels tender and heartfelt. Romantic style, soft lighting, warm colors, intimate composition. 8K resolution, dreamy aesthetic, emotional connection.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Romance.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Thriller',
                'category_prompt' => [
                    'concept_generator' => 'Write a suspenseful episode concept for "{series_name}", a Thriller web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on suspense, unexpected twists, hidden motives, psychological tension, and edge-of-your-seat moments. Include the thriller hook, escalating tension, a cat-and-mouse dynamic, and a jaw-dropping cliffhanger. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A suspenseful thriller scene from "{scene_title}". {scene_description} Dramatic shadows create tension. The character looks over their shoulder, sensing danger. Dutch angle adds unease. Dark figure lurks in the background. Thriller style, dramatic shadows, tense composition, mysterious atmosphere. 8K resolution, psychological tension, dramatic framing.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Thriller.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Mystery',
                'category_prompt' => [
                    'concept_generator' => 'Write an intriguing episode concept for "{series_name}", a Mystery web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on clues, investigations, hidden secrets, puzzle-solving, and surprising revelations. Include the mystery hook, a trail of clues, suspect dynamics, and a revelation that changes everything. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'A mysterious scene from "{scene_title}". {scene_description} The detective examines evidence. Shadows and fog create mystery. Noir lighting with venetian blind shadows. Clues hidden in the environment. Mystery style, noir lighting, shadowy colors, enigmatic composition. 8K resolution, detective framing, enigmatic atmosphere.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Mystery.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Adventure',
                'category_prompt' => [
                    'concept_generator' => 'Write an exciting episode concept for "{series_name}", an Adventure web series.

Idea: {user_prompt}

Write a 700-800 character concept that tells a complete story with a beginning, middle, and end. Focus on exploration, discovery, epic journeys, treasure hunting, and overcoming obstacles. Include the adventure hook, dangerous obstacles, character growth, and a discovery that propels the journey forward. Make it engaging and hook-driven. No labels, just the concept.',
                    
                    'image_generator' => 'An epic adventure scene from "{scene_title}". {scene_description} The hero stands at a breathtaking vista, looking toward the horizon. Vast landscapes stretch before them. Golden hour lighting creates dramatic shadows. Adventure style, wide shots, golden lighting, epic landscapes. 8K resolution, epic landscape photography, discovery aesthetic.'
                ],
                'init_image' => '/public/templates/categories_image/ai-Adventure.jpg',
                'is_active' => true,
            ],
        ];
        
        foreach ($templates as $template) {
            $category = Category::where('name', $template['category_name'])->first();
            
            if ($category) {
                CategoryTemplate::updateOrCreate(
                    ['category_id' => $category->id],
                    [
                        'category_prompt' => $template['category_prompt'],
                        'init_image' => $template['init_image'],
                        'is_active' => $template['is_active'],
                    ]
                );
            }
        }
    }
}