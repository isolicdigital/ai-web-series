@extends('layouts.blank')

@section('metadata')
    <title>Login Instructions - {{ config('app.name') }}</title>
    <meta name="description" content="Your login credentials and support information for {{ config('app.name') }}">
@endsection

@section('content')
<div class="support-page">
    <div class="header-logo">
        <a href="/" class="navbar-brand">
            <img src="{{ asset('custom/brand/frontend-logo.png') }}" alt="{{ config('app.name') }}">
        </a>
    </div>
    
    <div class="support-container">
        <div class="support-hero">
            <h1>Welcome to {{ config('app.name') }}</h1>
            <p class="support-subtitle">Your login information and support options</p>
        </div>
        
        <div class="simple-credentials">
            <div class="credentials-header">
                <div class="header-left">
                    <i class="fas fa-user-circle"></i>
                    <h3>Your Login Credentials</h3>
                </div>
                <button class="copy-all-btn" onclick="copyAllCredentials()">
                    <i class="fas fa-copy"></i>
                    Copy All
                </button>
            </div>
            <p class="credentials-intro">Thank you for purchasing <strong>{{ config('app.name') }}</strong>! Below are your login details:</p>
            
            <div class="credentials-list">
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-globe"></i>
                        <span>Login URL:</span>
                    </div>
                    <div class="credential-value">
                        <a href="{{ url('/') }}" target="_blank" class="url-link">
                            {{ url('/') }}
                            <i class="fas fa-external-link-alt external-icon"></i>
                        </a>
                    </div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-envelope"></i>
                        <span>Email:</span>
                    </div>
                    <div class="credential-value">
                        <span>Your Purchase Email Address</span>
                        <div class="info-tooltip">
                            <i class="fas fa-info-circle info-icon"></i>
                            <span class="tooltip-text">Use the email address associated with your purchase</span>
                        </div>
                    </div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-lock"></i>
                        <span>Password:</span>
                    </div>
                    <div class="credential-value">
                        <code id="password-text" class="password-text">{{ env('DEF_PW', 'default123') }}</code>
                        <button class="copy-btn" onclick="copyPassword()">
                            <i class="fas fa-copy"></i>
                            Copy
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="credentials-note">
                <p>Once logged in, you'll find all training materials in the <strong>left menu at the top</strong>. These resources will help you use the platform effectively.</p>
            </div>
        </div>

        <div class="support-card">
            <div class="card-content">
                <h3>Get Help When You Need It</h3>
                <p>Our support team is dedicated to helping you succeed:</p>
                
                <div class="support-notice-info">
                    <i class="fas fa-info-circle"></i>
                    <div class="support-notice-content">
                        <p><strong>Our support team is here to assist you as quickly as possible.</strong></p>
                        <p>Due to high demand and different time zones, we operate on a first-come, first-served basis, with a response time of 24–48 hours (excluding Sundays).</p>
                        <p>Your satisfaction is our top priority. We truly appreciate your patience and understanding during this busy time.</p>
                    </div>
                </div>

                <!-- <div class="channels-grid"> -->
                    <div class="channel">
                        <div class="channel-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="channel-info">
                            <h4>Support Team</h4>
                            <p>Direct message support for all your questions</p>
                            <a href="{{ env('SUPPORT_EXT', '#') }}" class="channel-action" target="_blank">
                                Open Desk
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <!-- </div> -->

                <div class="support-notice">
                    <i class="fas fa-lightbulb"></i>
                    <p><strong>Pro Tip:</strong> Most questions can be answered quickly in our training materials. Check there first to save time!</p>
                </div>
            </div>
        </div>

        <div class="support-card welcome-message">
            <div class="card-content">
                <div class="welcome-header">
                    <h3>Welcome to the Future of AI</h3>
                </div>
                <p class="welcome-text">
                    Thank you for choosing <strong>{{ config('app.name') }}</strong>. We're thrilled to have you join our community of innovators and creators. Our team is committed to providing you with the tools and support you need to achieve remarkable results.
                </p>
                <div class="welcome-features">
                    <div class="feature-highlight">
                        <i class="fas fa-bolt"></i>
                        <span>Powerful AI Technology</span>
                    </div>
                    <div class="feature-highlight">
                        <i class="fas fa-shield-alt"></i>
                        <span>Enterprise-Grade Security</span>
                    </div>
                    <div class="feature-highlight">
                        <i class="fas fa-sync"></i>
                        <span>Continuous Updates</span>
                    </div>
                </div>
                <div class="signature">
                    <strong>Ready to create something amazing?</strong>
                    <p>The {{ config('app.name') }} Team</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="global-toast" class="global-toast">
    <i class="fas fa-check-circle"></i>
    <span id="toast-message">Copied to clipboard!</span>
</div>

<script>
    function copyPassword() {
        const passwordText = document.getElementById('password-text').textContent;
        copyToClipboard(passwordText, 'Password');
    }

    function copyAllCredentials() {
        const introText = document.querySelector('.credentials-intro').textContent;
        const credentialsList = document.querySelector('.credentials-list');
        
        let credentialsText = '';
        const credentialItems = credentialsList.querySelectorAll('.credential-item');
        
        credentialItems.forEach(item => {
            const label = item.querySelector('.credential-label span').textContent;
            let value = '';
            
            if (item.querySelector('.url-link')) {
                value = item.querySelector('.url-link').textContent.trim();
            } else if (item.querySelector('.password-text')) {
                value = item.querySelector('.password-text').textContent;
            } else {
                value = item.querySelector('.credential-value span').textContent;
            }
            
            credentialsText += `${label} ${value}\n`;
        });
        
        const fullText = `${introText}\n\n${credentialsText}`;
        copyToClipboard(fullText, 'All credentials');
    }

    function copyToClipboard(text, type) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(`${type} copied to clipboard!`);
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast(`${type} copied to clipboard!`);
        });
    }

    function showToast(message) {
        const toast = document.getElementById('global-toast');
        const toastMessage = document.getElementById('toast-message');
        
        toastMessage.textContent = message;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
</script>
@endsection