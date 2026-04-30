<?php

namespace App\Http\Controllers;

use App\Models\AiCustomGenerator;
use App\Models\AiGeneration;
use App\Helpers\AiGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentsController extends Controller
{
    public function index()
    {
        $tools = AiCustomGenerator::where('active', 1)->get();
        return view('tools.index', compact('tools'));
    }
        
    public function automation()
    {
        $tools = AiCustomGenerator::where('active', 1)->where('premium', 4)->get();
        $title = 'Automation Suite';
        $subtitle = 'Build powerful systems to automate workflows, marketing, and operations';
        $routePrefix = 'automation';
        $icon = 'fa-robot';
        
        return view('tools.index', compact('tools', 'title', 'subtitle', 'routePrefix', 'icon'));
    }

    public function monetization()
    {
        $tools = AiCustomGenerator::where('active', 1)->where('premium', 3)->get();
        $title = 'Monetization Kit';
        $subtitle = 'Turn your content, traffic, and audience into consistent revenue';
        $routePrefix = 'monetization';
        $icon = 'fa-chart-line';
        
        return view('tools.index', compact('tools', 'title', 'subtitle', 'routePrefix', 'icon'));
    }

    public function accelerator()
    {
        $tools = AiCustomGenerator::where('active', 1)->where('premium', 5)->get();
        $title = 'Publishing Accelerator';
        $subtitle = 'Create, scale, and publish high-quality AI-driven content series effortlessly';
        $routePrefix = 'accelerator';
        $icon = 'fa-rocket';
        
        return view('tools.index', compact('tools', 'title', 'subtitle', 'routePrefix', 'icon'));
    }

    public function show($slug)
    {
        $tool = AiCustomGenerator::where('slug', $slug)->where('active', 1)->firstOrFail();
        
        // Fetch user's previous generations for this specific tool
        $generations = AiGeneration::where('user_id', auth()->id())
                                   ->where('ai_custom_generator_id', $tool->id)
                                   ->latest()
                                   ->get();

        return view('tools.show', compact('tool', 'generations'));
    }

    public function video()
    {
        return view('tools.video');
    }

    public function generate(Request $request, $slug)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'prompt' => 'required|string|min:3|max:5000',
            'tone' => 'nullable|string',
            'format' => 'nullable|string',
            'creativity' => 'nullable|integer|min:1|max:5',
            'length' => 'nullable|integer|min:1|max:5',
        ]);

        $tool = AiCustomGenerator::where('slug', $slug)->where('active', 1)->firstOrFail();
        $user = auth()->user();

        $lengths = [
            1 => 'very short and concise', 
            2 => 'short', 
            3 => 'medium-length', 
            4 => 'detailed and long', 
            5 => 'highly detailed, comprehensive, and very long'
        ];
        
        $creativity = [
            1 => 0.2, 2 => 0.5, 3 => 0.7, 4 => 0.9, 5 => 1.1 
        ];

        $selectedLength = $lengths[$request->length] ?? 'medium-length';
        $selectedTone = $request->tone ?? 'professional';
        $selectedFormat = $request->format ?? 'paragraph';
        $temperature = $creativity[$request->creativity] ?? 0.7;

        $promptTemplate = $tool->prompt_template ?? 'Generate content about: {{prompt}}';
        $fullPrompt = str_replace('{{prompt}}', $request->prompt, $promptTemplate);

        $systemPrompt = "You are an expert content creator specializing in {$tool->title}. " .
                        ($tool->description ?? '') . " " .
                        "CRITICAL INSTRUCTIONS: " .
                        "1. Your response MUST be written in a {$selectedTone} tone. " .
                        "2. The format of your output MUST be: {$selectedFormat}. Use standard Markdown for all formatting. " .
                        "3. The length of the content MUST be {$selectedLength}.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $fullPrompt]
        ];

        return response()->stream(function () use ($messages, $temperature, $request, $tool, $user) {
            
            if (function_exists('ini_set')) {
                ini_set('output_buffering', 'off');
                ini_set('zlib.output_compression', false);
            }

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $ch = curl_init();
            
            // This will hold the complete AI response
            $completeGeneratedText = ''; 
            
            curl_setopt($ch, CURLOPT_URL, 'https://api.deepseek.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('DS_KEY')
            ]);

            $maxTokens = 2000; 

            $data = [
                'model' => 'deepseek-chat',
                'messages' => $messages,
                'stream' => true,
                'max_tokens' => $maxTokens,
                'temperature' => (float)$temperature
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            // Pass $completeGeneratedText by reference (&$completeGeneratedText) to append to it
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$completeGeneratedText) {
                $lines = explode("\n", $chunk);
                
                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {
                        $dataChunk = substr($line, 6);
                        
                        if (trim($dataChunk) === '[DONE]') {
                            echo "data: [DONE]\n\n";
                            flush(); 
                            return strlen($chunk);
                        }
                        
                        $json = json_decode($dataChunk, true);
                        if (isset($json['choices'][0]['delta']['content'])) {
                            $content = $json['choices'][0]['delta']['content'];
                            
                            // Append the chunk to our full text variable
                            $completeGeneratedText .= $content;
                            
                            echo "data: " . json_encode(['content' => $content]) . "\n\n";
                            flush(); 
                        }
                    }
                }
                return strlen($chunk);
            });
            
            curl_exec($ch);
            curl_close($ch);
            
            // STREAM FINISHED: Save the fully generated text to the database
            if (!empty($completeGeneratedText)) {
                AiGeneration::create([
                    'user_id' => $user->id,
                    'ai_custom_generator_id' => $tool->id,
                    'title' => $request->title,
                    'prompt' => $request->prompt,
                    'tone' => $request->tone,
                    'format' => $request->format,
                    'creativity' => $request->creativity,
                    'length' => $request->length,
                    'output' => $completeGeneratedText
                ]);
            }
            
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive'
        ]);
    }
}