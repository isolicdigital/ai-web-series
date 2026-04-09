<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\IpnHit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


use App\Models\VideoCreditPlan;
use App\Models\UserVideoCredit;
use App\Models\VideoCreditPurchase;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plan = Plan::where('order', 1)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'agency_id' => null,
            'is_agency_owner' => false,
            'status' => 'active',
            'role' => 'user',
        ]);

        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYears(10),
            'status' => 'active',
        ]);

        event(new Registered($user));

        // Add to AWeber
        $this->addToAWeberList($request->email, $request->name);

        // Send welcome email with password
        $this->sendWelcomeEmail($request->name, $request->email, $request->password);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
    
    /**
     * Handle remote registration from payment gateways (EXP, Zoo, LP, etc.)
     */
    public function register_remote(Request $request, $channel)
    {
        Log::info("Starting register_remote function", ['channel' => $channel, 'ip' => $request->ip()]);

        // Extract data based on channel
        $extractedData = $this->extractChannelData($request, $channel);
        $userName = $extractedData['user_name'];
        $userEmail = $extractedData['user_email'];
        $productId = $extractedData['product_id'];
        $purchaseId = $extractedData['purchase_id'];
        $purchaseType = $extractedData['purchase_type'];
        $purchaseStates = $extractedData['purchase_states'];

        // Store IPN webhook response
        IpnHit::create([
            'email' => $userEmail,
            'channel' => $channel,
            'timestamp' => now(),
            'data' => $request->all()
        ]);

        // Find the plan
        $plan = Plan::where($channel . '_id', $productId)->first();

        if (!$plan) {
            Log::error("Plan not found", ['channel' => $channel, 'product_id' => $productId]);
            return response()->json(['error' => 'Plan not found'], 404);
        }

        $user = User::where('email', $userEmail)->first();

        if (!$user) {
            // Create new user
            return $this->createNewUserWithSubscription($userName, $userEmail, $plan, $channel, $purchaseId);
        } else {
            // Handle existing user (upgrade)
            return $this->handleExistingUser($user, $plan, $purchaseType, $purchaseStates, $purchaseId);
        }
    }

    /**
     * Extract channel-specific data from request
     */
    private function extractChannelData(Request $request, $channel)
    {
        if ($channel === 'exp') {
            return [
                'user_name' => $request->customerName,
                'user_email' => $request->customerEmail,
                'product_id' => $request->productId,
                'purchase_type' => $request->type,
                'purchase_states' => ['sale']
            ];
        } elseif ($channel === 'zoo') {
            return [
                'user_name' => $request->ccustname==NULL?explode('@',$request->ccustemail)[0]:$request->ccustname,
                'user_email' => $request->ccustemail,
                'product_id' => $request->cproditem,
                'purchase_id' => $request->ctransreceipt,
                'purchase_type' => $request->ctransaction,
                'purchase_states' => ['SALE']
            ];
        } elseif ($channel === 'lp') {
            return [
                'user_name' => $request->user['name'],
                'user_email' => $request->user['email'],
                'product_id' => $request->product_id,
                'purchase_type' => $request->action,
                'purchase_states' => ['SALE']
            ];
        } else {
            // Default WP handling
            return [
                'user_name' => $request->WP_BUYER_NAME,
                'user_email' => $request->WP_BUYER_EMAIL,
                'product_id' => $request->WP_ITEM_NUMBER,
                'purchase_type' => $request->WP_ACTION,
                'purchase_states' => ['sale', 'subscr_created', 'subscr_completed', 'subscr_reactivated']
            ];
        }
    }

    /**
     * Create new user with subscription
     */
    private function createNewUserWithSubscription($userName, $userEmail, $plan, $channel, $purchaseId)
    {
        try {
            Log::info("Creating new user", ['email' => $userEmail, 'name' => $userName]);

            $password = env('DEF_PW', 'default123');
            
            $user = User::create([
                'name' => $userName,
                'email' => $userEmail,
                'password' => Hash::make($password),
                'status' => 'active',
                'role' => 'user',
            ]);

            // Create subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
                'status' => 'active',
            ]);

            // Check if user already has credits (to avoid duplicate)
            $existingCredits = UserVideoCredit::where('user_id', $user->id)->first();
            
            if (!$existingCredits) {
                $creditsToAdd = ($plan->id == 8) ? 10000 : 5000;

                // Allocate credits for new user
                UserVideoCredit::create([
                    'user_id' => $user->id,
                    'free_credits' => $creditsToAdd,
                    'free_credits_used' => 0,
                    'paid_credits' => 0,
                    'paid_credits_used' => 0,
                    'total_credits' => $creditsToAdd,
                    'used_credits' => 0,
                ]);

                VideoCreditPurchase::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'SIGNUP_' . $purchaseId,
                    'credits_purchased' => $creditsToAdd,
                    'credits_used' => 0,
                    'expires_at' => null,
                    'is_active' => true
                ]);
            }

            // Add to AWeber
            $this->addToAWeberList($userEmail, $userName);

            // Send welcome email with password
            $this->sendWelcomeEmail($userName, $userEmail, $password);

            Log::info("User created successfully", ['user_id' => $user->id]);

            return response()->json([
                'error' => false,
                'message' => 'Signup successful! Welcome email sent!'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create user with subscription", [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle existing user upgrade
     */
    private function handleExistingUser($user, $plan, $purchaseType, $purchaseStates, $purchaseId)
    {
        Log::info("User already exists", ['user_id' => $user->id, 'email' => $user->email]);

        $transactionState = in_array($purchaseType, $purchaseStates) ? 'purchase' : 'refund';

        if ($transactionState == 'purchase') {
            // Get current subscription
            $currentSubscription = $user->subscriptions()->where('status', 'active')->first();
            $currentPlanId = $currentSubscription ? $currentSubscription->plan_id : null;
            
            // Get user credits
            $userCredit = UserVideoCredit::where('user_id', $user->id)->first();
            
            if (!$userCredit) {
                $userCredit = UserVideoCredit::create([
                    'user_id' => $user->id,
                    'total_credits' => 0,
                    'used_credits' => 0,
                    'free_credits' => 0,
                    'free_credits_used' => 0,
                    'paid_credits' => 0,
                    'paid_credits_used' => 0
                ]);
            }
            
            // Calculate credits to add based on current plan and new plan
            $creditsToAdd = 0;

            if ($plan->id == 8) { // Enterprise Plan
                // Always add 5,000 on top of existing (total becomes 10,000)
                $creditsToAdd = 5000;
            } else {
                // Plan 1-7: No additional credits on upgrade
                $creditsToAdd = 0;
            }
            
            if ($creditsToAdd > 0) {
                $userCredit->free_credits += $creditsToAdd;
                $userCredit->total_credits += $creditsToAdd;
                $userCredit->save();
                
                VideoCreditPurchase::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'UPGRADE_' . $purchaseId,
                    'credits_purchased' => $creditsToAdd,
                    'credits_used' => 0,
                    'expires_at' => null,
                    'is_active' => true
                ]);
                
                Log::info("Free credits added for upgrade", [
                    'user_id' => $user->id,
                    'new_plan_id' => $plan->id,
                    'previous_plan_id' => $currentPlanId,
                    'credits_added' => $creditsToAdd
                ]);
            }
            
            // Update subscription
            $user->subscriptions()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addYears(10),
                    'status' => 'active',
                ]
            );

            Log::info("User upgraded successfully", [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'previous_plan_id' => $currentPlanId
            ]);
            
            return response()->json(['status' => 'Upgrade done!'], 200);
        } else {
            // Refund - delete user
            Log::warning("Processing refund - deleting user", ['user_id' => $user->id]);
            $user->delete();

            return response()->json(['status' => 'User deleted!'], 200);
        }
    }

    /**
     * Add user to AWeber
     */
    private function addToAWeberList($email, $name)
    {
        try {
            // Replace with your AWeber API endpoint
            $apiUrl = config('services.aweber.endpoint', 'https://softprohub.com/api/aweber/subscribe');
            $tag = config('app.name');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'email' => $email,
                'name' => $name,
                'source' => config('app.name'),
                'app_id' => config('app.name'),
                'tags' => [$tag],
            ]);

            if ($response->successful()) {
                Log::info('User added to AWeber', ['email' => $email]);
                return true;
            }

            Log::warning('Failed to add user to AWeber', ['email' => $email, 'status' => $response->status()]);
            return false;

        } catch (\Exception $e) {
            Log::error('AWeber API call failed', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($userName, $userEmail, $password)
    {
        try {
            $appName = config('app.name');
            $loginUrl = url('/login');
            $supportDesk = 'https://app.aistandup.live/support/';

            Mail::send('emails.welcome', [
                'name' => $userName,
                'email' => $userEmail,
                'password' => $password,
                'app_name' => $appName,
                'login_url' => $loginUrl,
                'support_desk' => $supportDesk
            ], function ($message) use ($userEmail, $userName, $appName) {
                $message->to($userEmail, $userName)
                    ->subject("Welcome to {$appName} - Your Account Details");
            });

            Log::info("Welcome email sent", ['email' => $userEmail]);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send welcome email", ['email' => $userEmail, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function handlePaidTokens(Request $request)
    {
        // Extract data from JVZoo webhook
        $customerEmail = $request->input('ccustemail') ?? $request->input('customer_email') ?? $request->input('email');
        $productId = $request->input('cproditem');
        $transactionId = $request->input('ctransreceipt');
        
        // Store IPN webhook response
        $ipnHit = IpnHit::create([
            'email' => $customerEmail ?? 'unknown',
            'channel' => 'jvzoo',
            'timestamp' => now(),
            'data' => $request->all()
        ]);

        Log::info('Paid tokens webhook received', [
            'ip' => $request->ip(),
            'customer_email' => $customerEmail,
            'product_id' => $productId,
            'transaction_id' => $transactionId
        ]);

        // Find the plan by product ID
        $plan = VideoCreditPlan::where('wp_id', $productId)->first();
        
        if (!$plan) {
            Log::warning('No matching plan found for product', ['product_id' => $productId]);
            return response()->json(['message' => 'Plan not found'], 200);
        }

        // Find user by email
        $user = User::where('email', $customerEmail)->first();
        
        if (!$user) {
            Log::warning('No user found for email', ['email' => $customerEmail]);
            return response()->json(['message' => 'User not found'], 200);
        }

        // Check if transaction already processed
        $existingPurchase = VideoCreditPurchase::where('transaction_id', $transactionId)->first();
        if ($existingPurchase) {
            Log::info('Transaction already processed', ['transaction_id' => $transactionId]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        // If Plan 8 (Enterprise) purchased, clear temp block cache
        if ($plan->id == 8) {
            $cacheKey = 'video_gen_block_' . $user->id;
            Cache::forget($cacheKey);
            Log::info('Temp block cleared for user after Plan 8 purchase', ['user_id' => $user->id]);
        }

        // Add credits to user
        $userCredit = UserVideoCredit::where('user_id', $user->id)->first();
        
        if (!$userCredit) {
            $userCredit = UserVideoCredit::create([
                'user_id' => $user->id,
                'total_credits' => 0,
                'used_credits' => 0,
                'free_credits' => 0,
                'free_credits_used' => 0,
                'paid_credits' => 0,
                'paid_credits_used' => 0
            ]);
        }

        // Add paid credits
        $userCredit->paid_credits += $plan->video_credits;
        $userCredit->total_credits += $plan->video_credits;
        $userCredit->save();

        // Create purchase record
        VideoCreditPurchase::create([
            'user_id' => $user->id,
            'transaction_id' => 'PAID_' . $transactionId,
            'credits_purchased' => $plan->video_credits,
            'credits_used' => 0,
            'expires_at' => null,
            'is_active' => true
        ]);

        Log::info('Credits added successfully', [
            'user_id' => $user->id,
            'email' => $customerEmail,
            'credits_added' => $plan->video_credits,
            'plan_name' => $plan->name,
            'transaction_id' => $transactionId
        ]);

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Test email sending
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            Mail::raw('This is a test email from AI StandUp. If you receive this, your mail configuration is working correctly.', function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Test Email from AI StandUp');
            });

            Log::info('Test email sent', ['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->email
            ]);

        } catch (\Exception $e) {
            Log::error('Test email failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}