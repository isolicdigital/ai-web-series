<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PageBuilderSaves;

class PageBuilderController extends Controller
{

    public function index(Request $request)
    {
        try {
            return view('page_builder.menu_block', [
                'page_title' => 'Profit Pages'
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@index failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }
    
    public function siteCloner(Request $request)
    {
        try {
            $saves = PageBuilderSaves::where('user_id', auth()->id())->latest()->get();
            
            return view('page_builder.site_cloner', [
                'page_title' => 'Site Cloner',
                'saves' => $saves
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@siteCloner failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function createNew(Request $request, $cat = 'landing_pages')
    {
        try {
            $cats = [
                'landing_pages'=>'Landing Pages',
                'optin_pages'=>'OptIn Pages',
                'sign_up_pages'=>'Sign Up Pages',
                'coming_soon_pages'=>'Coming Soon Pages',
                'checkout_pages'=>'Checkout Pages',
                'webinar_pages'=>'Webinar Pages',
                'thank_you_pages'=>'Thank You Pages',
                'giveaway_pages'=>'Giveaway Pages'
            ];
            return view('page_builder.create_new', [
                'basic' => 1,
                'temp_dir' => $cat,
                'cats' => $cats,
                'page_title' => 'High-Converting Templates',
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@createNew failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function dfy(Request $request, $cat = 'digital_agency')
    {
        try {
            $cats = [
                'automotive_cars'=>'Automotive & Cars',
                'books_publishers'=>'Books & Publishers',
                'consulting_coaching'=>'Consulting & Coaching',
                'digital_agency'=>'Digital Agency',
                'education'=>'Education',
                'events'=>'Events',
                'fashion_beauty'=>'Fashion & Beauty',
                'health_wellness'=>'Health & Wellness',
                'online_store'=>'Online Store',
                'real_estate'=>'Real Estate',
                'restaurants_food'=>'Restaurants & Food',
                'services_maintenance'=>'Services & Maintenance',
                'tech_apps'=>'Technology & Apps',
                'travel_tourism'=>'Travel & Tourism',
            ];
            return view('page_builder.create_new', [
                'temp_dir' => $cat,
                'cats' => $cats,
                'page_title' => 'DFY Templates'
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@dfy failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function traffic(Request $request)
    {
        try {
            $social = DB::table('social_share_links')->get();
            return view('page_builder.traffic', [
                'page_title' => 'Traffic Sources',
                'social' => $social
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@traffic failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function showeditor(Request $request, $id, $title)
    {
        try {
            $title = base64_decode($title);
            $layouts = $this->scanTemplates('layouts', $id);
        
            $url = $request->input('url');
            $page_url = base64_decode($url);
            $app_url = config('app.url');
        
            $page_host = parse_url($page_url, PHP_URL_HOST);
            $page_host = Str::replaceFirst('www.', '', $page_host);
        
            $app_host = parse_url($app_url, PHP_URL_HOST);
            $app_host = Str::replaceFirst('www.', '', $app_host);
        
            $templateList = [];
            $isForm = false;
            $isLegacy = true;
        
            // Check if the project is already saved in the DB
            $existing = PageBuilderSaves::where('slug', $id)->first();
        
            if ($existing) {
                $cat = $existing->template_type;
                $dir = $existing->template_id;
                $page_path = "/pages/{$id}";
            } else {
                if ($page_host === $app_host) {
                    // Internal template path
                    preg_match('#/builder/assets/templates/([^/]+)/([^/]+)/#', $page_url, $matches);
                    $cat = $matches[1] ?? null;
                    $dir = $matches[2] ?? null;
                    if (!$cat || !$dir) {
                        abort(400, 'Invalid internal path structure.');
                    }
                    $templateList = $this->scanTemplates($cat, $id);
                } else {
                    // Remote page import
                    $cat = 'user_imports';
                    $dir = $id;
                    // $cloneRequest = self::cloneFromUrl($page_url, $id);
                    // if (!(json_decode($cloneRequest->getContent())->success ?? false)) {
                    //     abort(500, 'Failed to clone remote page.');
                    // }
                }
        
                $page_path = "/builder/assets/templates/{$cat}/{$dir}";
            }
        
            $formFields = [];
            if ($isForm) {
                $formFields = [
                    ["type"=>"email","label"=>"Email","name"=>"EMAIL","visible"=>"1","default"=>"","options"=>[]],
                    ["type"=>"text","label"=>"First name","name"=>"FIRST_NAME","visible"=>"1","default"=>"","options"=>[]],
                    ["type"=>"text","label"=>"Last name","name"=>"LAST_NAME","visible"=>"1","default"=>"","options"=>[]],
                    ["type"=>"radio","label"=>"Gender","name"=>"GENDER","visible"=>"1","default"=>"","options"=>[["value"=>"male","text"=>"Male"],["value"=>"female","text"=>"Female"]]],
                    ["type"=>"date","label"=>"Birthday","name"=>"BIRTHDAY","visible"=>"1","default"=>"","options"=>[]],
                    ["type"=>"rating","label"=>"Rating","name"=>"RATING","visible"=>"0","default"=>"5","options"=>[]],
                    ["type"=>"ip","label"=>"IP Address","name"=>"IP","visible"=>"0","default"=>"","options"=>[]],
                    ["type"=>"dropdown","label"=>"Timezone","name"=>"TIMEZONE","visible"=>"1","default"=>"","options"=>[["value"=>"1","text"=>"HCM"],["value"=>"2","text"=>"US"],["value"=>"3","text"=>"UK"]]],
                    ["type"=>"number","label"=>"Age","name"=>"AGE","visible"=>"1","default"=>"18","options"=>[]],
                    ["type"=>"multiselect","label"=>"Favourite","name"=>"FAVOURITE","visible"=>"1","default"=>"","options"=>[["value"=>"1","text"=>"Dog"],["value"=>"2","text"=>"Cat"],["value"=>"3","text"=>"Dinosaur"]]],
                    ["type"=>"checkbox","label"=>"Books","name"=>"BOOK","visible"=>"1","default"=>"","options"=>[["value"=>"1","text"=>"Comic books"],["value"=>"2","text"=>"Novel books"]]],
                    ["type"=>"textarea","label"=>"Message","name"=>"MESSAGE","visible"=>"1","default"=>"","options"=>[]],
                ];
            }
        
            return view('page_builder.editor', compact(
                'id', 'title', 'cat', 'dir', 'page_path', 'layouts', 'templateList', 'isForm', 'isLegacy', 'formFields'
            ));
        } catch (\Exception $e) {
            Log::error('PageBuilderController@showeditor failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function saveEditor(Request $request, $id, $title, $cat, $dir)
    {
        try {
            $content = $request->input('content');

            $destPath = public_path("pages/{$id}/");
            $indexPath = $destPath . 'index.html';

            // If first time saving, copy template files
            if (!File::exists($destPath)) {
                $sourcePath = public_path("builder/assets/templates/{$cat}/{$dir}");
                if (!File::exists($sourcePath)) {
                    return response()->json(['error' => 'Template not found.'], 404);
                }

                File::copyDirectory($sourcePath, $destPath);

                // Delete the source directory if it's a user_import
                if ($cat === 'user_imports') {
                    File::deleteDirectory($sourcePath);
                }
            }

            // Always update index.html
            file_put_contents($indexPath, $content);

            // Always update thumbnail if provided
            if ($request->hasFile('thumbnail')) {
                $request->file('thumbnail')->move($destPath, 'thumb.png');
            }

            // Only create DB record if not already present
            PageBuilderSaves::firstOrCreate(
                ['slug' => $id],
                [
                    'user_id' => auth()->id(),
                    'title' => base64_decode($title),
                    'template_type' => $cat,
                    'template_id' => $dir,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Page saved successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@saveEditor failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function scanTemplates($cat, $id)
    {
        try {
            $basePath = public_path("builder/assets/templates/$cat");
            $templateUrl = File::directories($basePath);
            $templates = [];
        
            foreach ($templateUrl as $path) {
                $name = basename($path);
                $fileHtml = "$path/index.html";
                $filePhp = "$path/index.php";
        
                if (File::exists($fileHtml) || File::exists($filePhp)) {
                    $file = File::exists($fileHtml) ? $fileHtml : $filePhp;
                    $content = File::get($file);
                    preg_match('/<title>(.*?)<\/title>/i', $content, $matches);
                    $title = $matches[1] ?? $name;
        
                    $templates[] = [
                        'name' => $title,
                        'url' => route('page-builder.show', ['id' => $id, 'title' => base64_encode($title)]),
                        'thumbnail' => "/builder/assets/templates/{$cat}/{$name}/thumb.png",
                    ];
                }
            }
        
            return $templates;
        } catch (\Exception $e) {
            Log::error('PageBuilderController@scanTemplates failed — ' . $e->getMessage());
            return [];
        }
    }

    public function cloneFromUrl(Request $request, $id)
    {
        $page_url = $request->input('url');
        $savePath = public_path("builder/assets/templates/user_imports/{$id}");

        try {
            // Validate input URL
            if (!filter_var($page_url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid URL.',
                ], 400);
            }

            // ========== SECURITY CHECKS ==========
            $parsedUrl = parse_url($page_url);
            $host = strtolower($parsedUrl['host'] ?? '');
            $fullUrl = strtolower($page_url);

            // Comprehensive blocklist for financial and sensitive sites
            $blockedPatterns = [
                // Banking domains
                '/\.bank$/i',
                '/\b(sbi|statebank|onlinesbi|sbicard|sbi\.co\.in)\b/i',
                '/\b(chase|wellsfargo|bankofamerica|bofa|citibank|citigroup|tdbank|hsbc|barclays|santander)\b/i',
                '/\b(usbank|capitalone|allybank|pnc|regions|suntrust|truist|fifththird|keybank|huntington)\b/i',
                '/\b(icici|hdfc|axisbank|pnb|kotak|yesbank|bankofbaroda|canarabank|unionbank)\b/i',
                
                // Payment processors
                '/\b(paypal|venmo|zelle|square|cashapp|stripe|wise|revolut|transferwise|skrill)\b/i',
                '/\b(payoneer|worldpay|adyen|braintree|authorize\.net|2checkout|wePay)\b/i',
                
                // Credit cards
                '/\b(americanexpress|amex|visa|mastercard|discover|dinersclub)\b/i',
                
                // Investment and crypto
                '/\b(fidelity|vanguard|schwab|charlesschwab|etrade|morganstanley|goldmansachs|merrilledge)\b/i',
                '/\b(coinbase|binance|kraken|gemini|crypto\.com|blockchain|bitstamp|bitfinex)\b/i',
                
                // Government
                '/\.gov$/i',
                '/\.mil$/i',
                '/\b(irs|socialsecurity|ssa|dmv|passport|uscis|state\.gov)\b/i',
                
                // Email and social media
                '/\b(gmail|outlook|yahoo|hotmail|protonmail|aol|icloud|zoho)\b/i',
                '/\b(facebook|twitter|instagram|linkedin|whatsapp|tiktok|snapchat|pinterest)\b/i',
                
                // Internal networks
                '/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.|localhost)/i',
            ];

            foreach ($blockedPatterns as $pattern) {
                if (preg_match($pattern, $host)) {
                    Log::warning("Blocked cloning attempt for sensitive domain", [
                        'url' => $page_url,
                        'host' => $host,
                        'user_id' => auth()->id() ?? 'unknown',
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'This type of website cannot be cloned for security reasons.',
                    ], 403);
                }
            }

            // Check for sensitive keywords
            $blockedKeywords = [
                'login', 'signin', 'account', 'banking', 'onlinebanking', 'secure', 'auth',
                'authentication', 'portal', 'myaccount', 'dashboard', 'transfer', 'payment',
                'billpay', 'wiretransfer', 'invest', 'trade', 'portfolio', 'corporate',
                'netbanking', 'netbank', 'net banking', 'corpbanking', 'retailbanking'
            ];

            foreach ($blockedKeywords as $keyword) {
                if (str_contains($fullUrl, $keyword)) {
                    Log::warning("Blocked cloning attempt for sensitive keyword", [
                        'url' => $page_url,
                        'keyword' => $keyword,
                        'user_id' => auth()->id() ?? 'unknown'
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'This type of website cannot be cloned for security reasons.',
                    ], 403);
                }
            }
            // ========== END SECURITY CHECKS ==========

            // Ensure save directory exists
            File::ensureDirectoryExists($savePath, 0777, true);

            // Clean up any pre-existing content in the folder
            File::cleanDirectory($savePath);

            // Try using wget first
            $escapedUrl = escapeshellarg($page_url);
            $escapedSavePath = escapeshellarg($savePath);
            $wgetCmd = "wget -k --html-extension -e robots=off $escapedUrl -P $escapedSavePath 2>&1";

            exec($wgetCmd, $wgetOutput, $wgetStatus);

            // Get .html files saved by wget
            $htmlFiles = collect(scandir($savePath))
                ->filter(fn($f) => Str::endsWith($f, '.html'))
                ->values()
                ->all();

            if ($wgetStatus === 0 && !empty($htmlFiles)) {
                File::move($savePath . '/' . $htmlFiles[0], $savePath . '/index.html');

                return response()->json([
                    'success' => true,
                    'message' => 'Page cloned successfully using wget.',
                ]);
            }
            
            Log::warning("Wget output", [
                'cmd' => $wgetCmd,
                'output' => $wgetOutput,
                'status' => $wgetStatus,
            ]);
            
            // If wget failed or no HTML file found, fallback to Puppeteer
            Log::warning("Wget failed or no HTML found. Trying Puppeteer for: $page_url");

            $scriptPath = base_path('scraper/fetch-with-puppeteer.cjs');
            $escapedScript = escapeshellarg($scriptPath);
            $escapedOutputFile = escapeshellarg($savePath . '/index.html');

            $proxyUrl = 'socks5://oc-f925b5101a1fc433537eb4d67dd287bdb8e7906890bd1657403534a9e54f09fa-country-US-session-c8032:nf0c9iep9h4e@proxy.oculus-proxy.com:31115';
            $proxyParts = parse_url($proxyUrl);
            $proxyArg = $proxyParts ? "{$proxyParts['scheme']}://{$proxyParts['user']}:{$proxyParts['pass']}@{$proxyParts['host']}:{$proxyParts['port']}" : '';
            $escapedProxy = escapeshellarg($proxyArg);

            exec("node $escapedScript $escapedUrl $escapedOutputFile $escapedProxy 2>&1", $puppeteerOutput, $puppeteerStatus);

            if ($puppeteerStatus !== 0 || !File::exists($savePath . '/index.html')) {
                Log::error("Puppeteer fetch failed for: $page_url", [
                    'output' => $puppeteerOutput,
                    'status' => $puppeteerStatus,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clone the page. Please try a different URL.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Page cloned using Puppeteer fallback.',
            ]);
        } catch (\Exception $e) {
            Log::error("cloneFromUrl failed for: $page_url — " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while cloning the page.',
            ], 500);
        }
    }
    
    private function isUrlAllowed(string $url): bool
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');
        
        // Allowlist approach (more secure)
        $allowedDomains = [
            'example.com',
            'yoursafedomain.com',
            // Add domains you explicitly allow
        ];
        
        // Or continue with blocklist but enhance it
        $blockedTlds = ['.gov', '.mil', '.int'];
        foreach ($blockedTlds as $tld) {
            if (str_ends_with($host, $tld)) {
                return false;
            }
        }
        
        return true;
    }

    public function saveAssets(Request $request, $id)
    {
        $savePath = public_path("builder/assets/templates/user_imports/{$id}");
    
        // Ensure directory exists
        if (!file_exists($savePath)) {
            mkdir($savePath, 0777, true);
        }
    
        $extension = '';
        $assetName = uniqid();
    
        if ($request->type === 'upload' && $request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = $assetName . '.' . $extension;
            $file->move($savePath, $fileName);
    
        } elseif ($request->type === 'url') {
            $url = $request->url;
            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            $extension = $pathInfo['extension'] ?? 'bin';
            $fileName = $assetName . '.' . $extension;
    
            $content = file_get_contents($url);
            file_put_contents($savePath . '/' . $fileName, $content);
    
        } elseif ($request->type === 'base64') {
            $base64 = $request->url_base64;
    
            // Try to extract extension from data URI (if available)
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $extension = $matches[1];
                $base64 = substr($base64, strpos($base64, ',') + 1); // Strip metadata
            } else {
                $extension = 'png'; // Fallback
            }
    
            $fileName = $assetName . '.' . $extension;
            file_put_contents($savePath . '/' . $fileName, base64_decode($base64));
    
        } else {
            return response()->json(['error' => 'Invalid asset upload type'], 400);
        }
    
        $url = "/builder/assets/templates/user_imports/{$id}/{$fileName}";
    
        return response()->json(['url' => $url], 200);
    }
    

    
    public function cloneFromUrl2(Request $request, $id)
    {
        $page_url = $request->input('url');
        $savePath = public_path("builder/assets/templates/user_imports/{$id}");

        try {
            if (!filter_var($page_url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid URL.',
                ], 400);
            }

            if (!File::exists($savePath)) {
                File::makeDirectory($savePath, 0777, true);
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])->get($page_url);

            $html = $response->body();
            $cloudflareTriggered = !$response->successful()
                || str_contains($html, 'Just a moment...')
                || str_contains($html, 'cf-chl-opt')
                || $response->status() === 403;

            if ($cloudflareTriggered) {
                Log::warning("Fallback to Puppeteer for: $page_url");

                $scriptPath = base_path('scraper/fetch-with-puppeteer.cjs');

                $escapedScript = escapeshellarg($scriptPath);
                $escapedUrl = escapeshellarg($page_url);
                $escapedPath = escapeshellarg($savePath . '/index.html');

                // Support full format: socks5://user:pass@host:port
                $proxyUrl = 'socks5://oc-f925b5101a1fc433537eb4d67dd287bdb8e7906890bd1657403534a9e54f09fa-country-US-session-c8032:nf0c9iep9h4e@proxy.oculus-proxy.com:31115';
                $proxyParts = parse_url($proxyUrl);

                $proxyArg = $proxyParts ? "{$proxyParts['scheme']}://{$proxyParts['user']}:{$proxyParts['pass']}@{$proxyParts['host']}:{$proxyParts['port']}" : '';
                exec("node $escapedScript $escapedUrl $escapedPath " . escapeshellarg($proxyArg) . " 2>&1", $output, $status);

                if ($status !== 0 || !File::exists($savePath . '/index.html')) {
                    Log::error("Puppeteer fetch failed for: $page_url", ['output' => $output, 'status' => $status]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Both fetch methods failed.',
                    ], 500);
                }
            } else {
                File::put($savePath . '/index.html', $html);
            }

            return response()->json([
                'success' => true,
                'message' => 'Page cloned successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("cloneFromUrl failed for: $page_url — " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }

    

    public function cloneFromUrl1(Request $request, $id)
    {
        $page_url = $request->input('url');
    
        try {
            $savePath = public_path("builder/assets/templates/user_imports/{$id}/");
    
            if (!File::exists($savePath)) {
                File::makeDirectory($savePath, 0777, true);
    
                if (!filter_var($page_url, FILTER_VALIDATE_URL)) {
                    abort(400, 'Invalid URL.');
                }

                // $command = "curl -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' -L " . escapeshellarg($page_url) . " --output {$savePath}index.html 2>&1";
                // // $command = "wget --user-agent='Mozilla/5.0' --no-check-certificate -k --html-extension -e robots=off " . escapeshellarg($page_url) . " -P {$savePath} 2>&1";
                // exec($command, $output);

                // Log::error('PageBuilderController@cloneFromUrl failed while wget ' . $page_url . ' — ' . json_encode($output));

                // $command = "wget -k --html-extension -e robots=off " . escapeshellarg($page_url) . " -P {$savePath} 2>&1";
                // exec($command);

                try {
                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    ])->get($page_url);
                
                    if ($response->successful()) {
                        File::put("{$savePath}/index.html", $response->body());
                    } else {
                        Log::error("HTTP request to clone page failed: {$page_url}", [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Exception during page cloning from {$page_url}: " . $e->getMessage());
                }
    
                // sleep(10); // wait for wget to finish
    
                // $htmlFile = collect(scandir($savePath))
                //     ->filter(fn($f) => str_ends_with($f, '.html'))
                //     ->first();
    
                // if (!$htmlFile) {
                //     abort(404, $savePath.' - No HTML content found.');
                // }
    
                // $html = file_get_contents("{$savePath}/{$htmlFile}");
                // file_put_contents("{$savePath}/index.html", $html);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Page cloned successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@cloneFromUrl failed while cloning ' . $page_url . ' — ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    

    public function maskedView(Request $request)
    {
        try {
            $slug = base64_decode($request->v);
            $path = public_path("pages/{$slug}/index.html");

            if (!file_exists($path)) {
                abort(404);
            }

            $html = file_get_contents($path);


            // Convert relative asset URLs (e.g., src="js/app.js") to absolute (e.g., src="/pages/{slug}/js/app.js")
            $baseUrl = url("/pages/{$slug}");

            // Update paths in src and href attributes
            $html = preg_replace_callback('/(src|href)=["\'](?!https?:|\/\/|\/)([^"\']+)["\']/', function ($matches) use ($baseUrl) {
                return $matches[1] . '="' . $baseUrl . '/' . ltrim($matches[2], '/') . '"';
            }, $html);
            
            return response($html)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('PageBuilderController@maskedView failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function savedPages(Request $request)
    {
        try {
            $saves = PageBuilderSaves::where('user_id', auth()->id())->latest()->get();
            return view('page_builder.saves', [
                'saves' => $saves,
                'page_title' => 'DFY Sites'
            ]);
        } catch (\Exception $e) {
            Log::error('PageBuilderController@savedPages failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function downloadPage(Request $request, $id)
    {
        try {
            $save = PageBuilderSaves::where('user_id', auth()->id())->findOrFail($id);
            $path = public_path("pages/{$save->template_id}");

            if (!File::exists($path)) {
                abort(404, 'Folder not found.');
            }

            $zipFile = storage_path("app/page-{$save->template_id}.zip");
            $zip = new \ZipArchive;
            if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($path) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
            }

            return response()->download($zipFile)->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('PageBuilderController@downloadPage failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }

    public function deletePage(Request $request, $id)
    {
        try {
            $save = PageBuilderSaves::where('user_id', auth()->id())->findOrFail($id);
            $path = public_path("pages/{$save->slug}");
            if (File::exists($path)) {
                File::deleteDirectory($path);
            }
            $save->delete();
            return redirect()->route('page-builder.saves')->with('success', 'Page deleted.');
        } catch (\Exception $e) {
            Log::error('PageBuilderController@deletePage failed at ' . $request->fullUrl() . ' — ' . $e->getMessage());
            abort(500);
        }
    }
}