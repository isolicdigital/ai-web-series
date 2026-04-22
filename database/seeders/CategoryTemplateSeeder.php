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

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on high-stakes action, heroic moments, intense combat sequences, and thrilling chase scenes. Include the protagonist\'s motivation, the central conflict, a key action set piece, and a cliffhanger ending. Make it engaging, detailed, and hook-driven. Use vivid descriptions and emotional depth. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial action scene image for "{scene_title}". {scene_description} The hero strikes a heroic pose, ready for battle. Explosions and sparks fill the background. Dynamic camera angle captures the intensity. Dramatic lighting with rim lights highlighting the hero. Fast-paced energy frozen in a single frame. Cinematic action movie style, high contrast lighting, dynamic composition. 8K resolution, professional stunt photography, film grain. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Action.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Look at the main subject - their pose, expression, and position
2. Study the background elements and environment
3. Note the lighting direction, shadows, and highlights
4. Identify any action elements (explosions, sparks, movement)
5. Observe the color palette and composition

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this exact image to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 30fps
- Match the EXACT style, lighting, colors, and mood from the analyzed image
- Opening (2 sec): Slow zoom or camera movement that follows the image\'s perspective
- Middle (6 sec): Animate elements from the image - make the character breathe slightly, add particle effects matching the image\'s explosions/sparks, subtle camera shake for action feel
- Closing (2 sec): Smooth transition or fade that preserves the image\'s atmosphere
- Sound effects: Explosions, punches, dramatic whooshes, tension building (matching the image\'s intensity)
- Voiceover: MALE voice only, deep and authoritative: "The battle begins... there is no turning back." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-action.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Drama',
                'category_prompt' => [
                    'concept_generator' => 'Write a powerful episode concept for "{series_name}", a Drama web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on emotional depth, character relationships, personal struggles, and meaningful moments of connection. Include the protagonist\'s internal conflict, a difficult choice they must face, a pivotal relationship moment, and an emotional climax. Make it engaging, detailed, and hook-driven. Use vivid descriptions and emotional depth. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial drama scene image for "{scene_title}". {scene_description} The character\'s face reveals deep emotion through subtle expressions. Soft lighting creates an intimate atmosphere. The composition focuses on emotional connection. Intimate dramatic style, soft natural lighting, shallow depth of field, warm earth tones. 8K resolution, emotional storytelling, filmic look. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Drama.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the character\'s facial expression - what emotion are they showing?
2. Look at the lighting - is it soft, harsh, warm, or cold?
3. Examine the background and atmosphere
4. Note any relationships or interactions visible
5. Observe the color palette and composition

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this emotional moment to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 24fps
- Match the EXACT emotional tone, lighting, and mood from the analyzed image
- Opening (2 sec): Slow fade-in from black, matching the image\'s mood
- Middle (6 sec): Subtle micro-movements - character breathing, eyes shifting slightly, soft focus breathing
- Closing (2 sec): Gentle fade that preserves the emotional impact
- Sound effects: Soft piano melody, gentle ambient sounds, subtle heartbeat (matching the image\'s emotion)
- Voiceover: MALE voice only, soft and vulnerable: "Sometimes, the hardest battles are the ones we fight alone." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-drama.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Comedy',
                'category_prompt' => [
                    'concept_generator' => 'Write a hilarious episode concept for "{series_name}", a Comedy web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on humorous situations, witty dialogue, comedic timing, and laugh-out-loud moments. Include the comedic premise, escalating absurdity, a funny set piece, and a satisfying punchline. Make it engaging, detailed, and hook-driven. Use vivid descriptions and comedic flair. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial comedy scene image for "{scene_title}". {scene_description} Characters show exaggerated expressions and body language. Bright, colorful setting enhances the lighthearted mood. Medium shot captures comedic timing perfectly. Bright comedy style, high-key lighting, vibrant cheerful colors, upbeat composition. 8K resolution, professional comedy timing, lively atmosphere. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Comedy.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Look at the characters\' expressions - are they surprised, laughing, confused?
2. Study the body language and poses
3. Note the bright colors and setting
4. Identify what makes this moment funny
5. Observe the composition and timing

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this funny moment to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 30fps
- Match the EXACT comedic tone, bright colors, and energy from the analyzed image
- Opening (1 sec): Quick zoom in on the funniest element
- Middle (7 sec): Playful camera shake, characters reacting, subtle motion that enhances the joke
- Closing (2 sec): Freeze frame on best expression + quick smash cut to black
- Sound effects: Comedic slide whistle, rimshot, audience laughter, upbeat quirky music
- Voiceover: MALE voice only, upbeat and energetic: "And that, my friends, is when it all went wrong... hilariously wrong." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-comedy.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Sci-Fi',
                'category_prompt' => [
                    'concept_generator' => 'Write a mind-bending episode concept for "{series_name}", a Sci-Fi web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on futuristic technology, advanced concepts, world-building, and mind-bending revelations. Include the sci-fi hook, world-building details, a philosophical question or ethical dilemma, and a mind-bending twist. Make it engaging, detailed, and hook-driven. Use vivid descriptions and imaginative concepts. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial sci-fi scene image for "{scene_title}". {scene_description} Advanced technology surrounds the characters. Neon lights reflect off sleek surfaces. Holographic displays float in the air. Cyberpunk aesthetic with atmospheric fog. Sci-fi style, neon lighting, futuristic colors, immersive world-building. 8K resolution, VFX quality, cyberpunk aesthetic. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Sci-Fi.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the futuristic technology visible
2. Look at the neon lighting and color palette
3. Note any holographic displays or digital elements
4. Observe the cyberpunk/futuristic atmosphere
5. Identify the main subjects and their relation to technology

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this futuristic scene to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 30fps
- Match the EXACT sci-fi aesthetic, neon colors, and futuristic feel from the analyzed image
- Opening (2 sec): Holographic glitch effect revealing the scene
- Middle (6 sec): Futuristic scan lines, neon glow pulses, data streams flowing, subtle holographic flickers
- Closing (2 sec): Digital dissolve to black with static interference
- Sound effects: Futuristic hum, electronic beeps, spaceship ambient, glitch sounds
- Voiceover: MALE voice only, digital and mysterious: "Welcome to tomorrow. Where humanity meets its destiny." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Sci-Fi.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Fantasy',
                'category_prompt' => [
                    'concept_generator' => 'Write an epic episode concept for "{series_name}", a Fantasy web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on magical elements, mythical creatures, enchanted settings, and epic adventures. Include the fantasy elements, a mythical creature or enchanted setting, the hero\'s call to adventure, and a spellbinding cliffhanger. Make it engaging, detailed, and hook-driven. Use vivid descriptions and magical imagery. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial fantasy scene image for "{scene_title}". {scene_description} Mythical creatures and magic glow in the air. Ancient ruins or enchanted forests provide the backdrop. Epic fantasy aesthetic with warm magical lighting. Fantasy style, magical lighting, enchanted colors, mystical atmosphere. 8K resolution, VFX quality, storybook aesthetic. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Fantasy.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Look at the magical elements and glowing effects
2. Study any mythical creatures or characters
3. Note the enchanted environment (forest, castle, ruins)
4. Observe the warm magical lighting
5. Identify the mystical atmosphere and colors

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this magical scene to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 24fps
- Match the EXACT fantasy aesthetic, magical lighting, and enchanted feel from the analyzed image
- Opening (2 sec): Magical sparkle particles floating across the image
- Middle (6 sec): Slow sweeping camera move, magical particles drifting, soft glow pulses, subtle lens flare
- Closing (2 sec): Dissolve to black with glowing ember effects
- Sound effects: Enchanting harp, magical chimes, gentle wind, mystical ambience
- Voiceover: MALE voice only, warm and wise: "In a world where magic awakens, the chosen one shall rise." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Fantasy.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Horror',
                'category_prompt' => [
                    'concept_generator' => 'Write a terrifying episode concept for "{series_name}", a Horror web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on suspense, fear, supernatural elements, psychological terror, and creeping dread. Include the horror premise, a suspenseful sequence, a shocking moment, and a lingering fear. Make it engaging, detailed, and hook-driven. Use vivid descriptions and atmospheric dread. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial horror scene image for "{scene_title}". {scene_description} Dark shadows hide unknown threats. Creepy atmosphere with low-key lighting. The character shows genuine fear through their expression. Something evil lurks just out of sight. Horror style, low-key lighting, dark desaturated colors, unsettling composition. 8K resolution, atmospheric dread, tense framing. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Horror.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the shadows and dark areas - where could threats hide?
2. Look at the character\'s fearful expression
3. Note the creepy atmosphere and lighting
4. Identify any potential jump scare elements
5. Observe the unsettling composition

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this terrifying moment to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 24fps
- Match the EXACT horror aesthetic, dark shadows, and dread from the analyzed image
- Opening (3 sec): Slow fade-in from pure black, subtle static noise
- Middle (5 sec): Slow, unsettling camera drift, shadows moving slightly, subtle flickering
- Closing (2 sec): Sudden cut to black with loud thud sound
- Sound effects: Creaking door, distant whisper, heartbeat increasing, jump scare thud
- Voiceover: MALE voice only, deep whisper: "It\'s watching you... and it knows you\'re alone." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Horror.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Romance',
                'category_prompt' => [
                    'concept_generator' => 'Write a heartwarming episode concept for "{series_name}", a Romance web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on emotional connection, relationship development, romantic moments, and heartfelt interactions. Include the romantic premise, emotional beats, an obstacle to overcome, and a swoon-worthy moment. Make it engaging, detailed, and hook-driven. Use vivid descriptions and emotional depth. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial romance scene image for "{scene_title}". {scene_description} Two people share an intimate connection. Soft lighting creates warm atmosphere. Their expressions show love and affection. The moment feels tender and heartfelt. Romantic style, soft lighting, warm colors, intimate composition. 8K resolution, dreamy aesthetic, emotional connection. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Romance.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the connection between the two people
2. Look at their expressions of love and affection
3. Note the soft lighting and warm atmosphere
4. Observe the intimate composition
5. Identify the romantic mood and colors

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this romantic moment to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 24fps
- Match the EXACT romantic aesthetic, soft lighting, and warm colors from the analyzed image
- Opening (2 sec): Soft fade-in from white
- Middle (6 sec): Slow orbit around the couple, soft focus, subtle light rays, gentle breathing
- Closing (2 sec): Dreamy dissolve to warm golden fade
- Sound effects: Soft acoustic guitar, gentle piano, romantic string melody
- Voiceover: MALE voice only, soft and tender: "In your eyes, I found my home... my forever." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Romance.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Thriller',
                'category_prompt' => [
                    'concept_generator' => 'Write a suspenseful episode concept for "{series_name}", a Thriller web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on suspense, unexpected twists, hidden motives, psychological tension, and edge-of-your-seat moments. Include the thriller hook, escalating tension, a cat-and-mouse dynamic, and a jaw-dropping cliffhanger. Make it engaging, detailed, and hook-driven. Use vivid descriptions and suspenseful language. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial thriller scene image for "{scene_title}". {scene_description} Dramatic shadows create tension. The character looks over their shoulder, sensing danger. Dutch angle adds unease. Dark figure lurks in the background. Thriller style, dramatic shadows, tense composition, mysterious atmosphere. 8K resolution, psychological tension, dramatic framing. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Thriller.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the dramatic shadows and lighting
2. Look at the character\'s paranoid expression
3. Note the dark figure or threat in background
4. Observe the tense composition (Dutch angle, framing)
5. Identify the suspenseful atmosphere

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this suspenseful moment to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 30fps
- Match the EXACT thriller aesthetic, dramatic shadows, and tension from the analyzed image
- Opening (2 sec): Slow reveal from darkness
- Middle (6 sec): Quick cuts, Dutch angle tilt, sudden zooms, paranoia effect, character looking around
- Closing (2 sec): Smash cut to black with sharp sound
- Sound effects: Ticking clock, footsteps, heavy breathing, sharp orchestral stab
- Voiceover: MALE voice only, low and tense: "You think you\'re safe... but they\'re already inside." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Thriller.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Mystery',
                'category_prompt' => [
                    'concept_generator' => 'Write an intriguing episode concept for "{series_name}", a Mystery web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on clues, investigations, hidden secrets, puzzle-solving, and surprising revelations. Include the mystery hook, a trail of clues, suspect dynamics, and a revelation that changes everything. Make it engaging, detailed, and hook-driven. Use vivid descriptions and enigmatic language. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial mystery scene image for "{scene_title}". {scene_description} The detective examines evidence. Shadows and fog create mystery. Noir lighting with venetian blind shadows. Clues hidden in the environment. Mystery style, noir lighting, shadowy colors, enigmatic composition. 8K resolution, detective framing, enigmatic atmosphere. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Mystery.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the detective or investigator figure
2. Look for hidden clues in the environment
3. Note the noir lighting and shadows
4. Observe the fog and mysterious atmosphere
5. Identify the enigmatic composition

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this mysterious scene to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 24fps
- Match the EXACT mystery aesthetic, noir lighting, and enigmatic feel from the analyzed image
- Opening (2 sec): Slow pan across scene, revealing hidden details
- Middle (6 sec): Subtle dolly zoom, clues highlighted with subtle glow, detective examining evidence
- Closing (2 sec): Slow fade to question mark fade-out
- Sound effects: Jazz noir saxophone, rain sounds, typewriter clicks, mysterious piano
- Voiceover: MALE voice only, deep and smooth: "The truth is hiding in plain sight... if you dare to look." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
                ],
                'init_image' => '/public/templates/categories_image/ai-Mystery.jpg',
                'is_active' => true,
            ],
            [
                'category_name' => 'Adventure',
                'category_prompt' => [
                    'concept_generator' => 'Write an exciting episode concept for "{series_name}", an Adventure web series.

Idea: {user_prompt}

Write a 900-1000 character concept that tells a complete story with a beginning, middle, and end. Focus on exploration, discovery, epic journeys, treasure hunting, and overcoming obstacles. Include the adventure hook, dangerous obstacles, character growth, and a discovery that propels the journey forward. Make it engaging, detailed, and hook-driven. Use vivid descriptions and epic language. No labels, just the concept.',
                    
                    'image_generator' => 'Generate an initial adventure scene image for "{scene_title}". {scene_description} The hero stands at a breathtaking vista, looking toward the horizon. Vast landscapes stretch before them. Golden hour lighting creates dramatic shadows. Adventure style, wide shots, golden lighting, epic landscapes. 8K resolution, epic landscape photography, discovery aesthetic. No text, no watermarks, no borders.',
                    
                    'video_generator' => 'You are given a generated image from scene "{scene_title}". Scene description: {scene_description}. Genre: Adventure.

ANALYZE THE PROVIDED IMAGE CAREFULLY:
1. Study the vast landscape and horizon
2. Look at the hero\'s pose and expression
3. Note the golden hour lighting
4. Observe the epic scale and composition
5. Identify the discovery/wonder aesthetic

Based on YOUR ANALYSIS of this SPECIFIC image, create a 10-second video that brings this epic adventure scene to life.

Video requirements:
- Duration: Exactly 10 seconds
- Cinematic 16:9 aspect ratio, 30fps
- Match the EXACT adventure aesthetic, golden lighting, and epic scale from the analyzed image
- Opening (2 sec): Wide shot, slow zoom into the horizon
- Middle (6 sec): Sweeping panoramic movement across landscape, sun rays moving, hero looking toward destination
- Closing (2 sec): Fade to golden sunset orange
- Sound effects: Epic orchestral swell, wind blowing, eagle cry, drums building
- Voiceover: MALE voice only, bold and heroic: "Beyond that horizon lies the greatest adventure ever told." (sent to user email)
- NO text overlays, NO watermarks

Output format: MP4, 1080p, H.264 codec'
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