<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $app_name }}</title>
    <style>
        /* Reset styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f7fb;
        }
        
        /* Main container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 48px 32px;
            text-align: center;
        }
        
        .email-header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        
        .email-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
        }
        
        /* Content */
        .email-content {
            padding: 40px 32px;
        }
        
        /* Welcome message */
        .welcome-text {
            margin-bottom: 32px;
        }
        
        .welcome-text h2 {
            color: #1e293b;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 16px 0;
        }
        
        .welcome-text p {
            color: #475569;
            font-size: 16px;
            margin: 0 0 16px 0;
        }
        
        /* Credentials box */
        .credentials-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
        }
        
        .credentials-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .credentials-title span {
            font-size: 24px;
        }
        
        .credential-row {
            margin-bottom: 16px;
        }
        
        .credential-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .credential-value {
            font-size: 16px;
            color: #1e293b;
            word-break: break-all;
        }
        
        .credential-value a {
            color: #8b5cf6;
            text-decoration: none;
        }
        
        .credential-value a:hover {
            text-decoration: underline;
        }
        
        .password-code {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 500;
            color: #6366f1;
            display: inline-block;
            letter-spacing: 0.5px;
        }
        
        /* Info boxes */
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 20px;
            border-radius: 8px;
            margin: 32px 0;
        }
        
        .info-box h4 {
            color: #166534;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        
        .info-box p {
            color: #14532d;
            font-size: 14px;
            margin: 0;
        }
        
        .support-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 32px 0;
        }
        
        .support-box h4 {
            color: #92400e;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        
        .support-box p {
            color: #78350f;
            font-size: 14px;
            margin: 0 0 16px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Footer */
        .email-footer {
            background: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .email-footer p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0 0 8px 0;
        }
        
        .email-footer a {
            color: #8b5cf6;
            text-decoration: none;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 24px 20px;
            }
            
            .email-header {
                padding: 32px 20px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 20px; background-color: #f4f7fb;">
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ $app_name }}</h1>
            <p>Your AI Journey Begins Here</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <div class="welcome-text">
                <h2>Hello {{ $name }},</h2>
                <p>Thank you for choosing <strong>{{ $app_name }}</strong>! We're thrilled to have you on board and can't wait to see what you'll create with our platform.</p>
                <p>Your account has been successfully created. Below are your login credentials to access the platform.</p>
            </div>
            
            <!-- Credentials -->
            <div class="credentials-box">
                <div class="credentials-title">
                    <span>🔐</span> Your Login Credentials
                </div>
                
                <div class="credential-row">
                    <div class="credential-label">Dashboard URL</div>
                    <div class="credential-value">
                        <a href="{{ $login_url }}">{{ $login_url }}</a>
                    </div>
                </div>
                
                <div class="credential-row">
                    <div class="credential-label">Email Address</div>
                    <div class="credential-value">{{ $email }}</div>
                </div>
                
                <div class="credential-row">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">
                        <code class="password-code">{{ $password }}</code>
                    </div>
                </div>
            </div>
            
            <!-- Quick tip -->
            <div class="info-box">
                <h4>🚀 Quick Tip</h4>
                <p>Once logged in, you'll find all training materials inside your dashboard. These resources will help you use the platform effectively.</p>
            </div>
            
            <!-- Support -->
            <div class="support-box">
                <h4>🛠️ Need Help?</h4>
                <p>Our support team is here to assist you 24/7. If you encounter any issues or have questions:</p>
                <a href="{{ config('app.support_desk', '#') }}" class="btn">Create Support Ticket</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p>Best wishes,</p>
            <p style="font-weight: 600; color: #1e293b;">The {{ $app_name }} Team</p>
            <p style="margin-top: 16px;">
                <a href="{{ $login_url }}">Login to Dashboard</a> | 
                <a href="{{ config('app.support_desk', '#') }}">Support Center</a>
            </p>
            <p style="margin-top: 16px;">&copy; {{ date('Y') }} {{ $app_name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>