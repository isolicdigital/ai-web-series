@extends('layouts.app')

@section('title', 'WhiteLabel Setup - ' . config('app.name'))

@section('css')
<style>
.whitelabel-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--glass-border);
}

.whitelabel-header {
    text-align: center;
    margin-bottom: 2rem;
}

.whitelabel-header img {
    max-width: 200px;
    margin-bottom: 1rem;
}

.whitelabel-header h1 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--text-main) 0%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}

.whitelabel-header p {
    color: var(--text-muted);
    font-size: 1rem;
}

.whitelabel-content {
    margin: 2rem 0;
}

.info-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--accent);
}

.info-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-main);
}

.steps-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.steps-list li {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--glass-border);
}

.steps-list li:last-child {
    border-bottom: none;
}

.step-number {
    width: 32px;
    height: 32px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--text-main);
}

.step-content p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.step-content code {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.8rem;
    display: inline-block;
    margin-top: 0.25rem;
}

.ip-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.ip-code {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.3rem 0.8rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.9rem;
}

.copy-ip-btn {
    background: transparent;
    border: 1px solid var(--glass-border);
    color: var(--text-muted);
    padding: 0.3rem 0.8rem;
    border-radius: 6px;
    font-size: 0.7rem;
    cursor: pointer;
    transition: var(--transition);
}

.copy-ip-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.support-section {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--glass-border);
}

.support-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--accent);
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.support-link:hover {
    background: var(--accent-dark);
    transform: translateY(-2px);
}

.support-text {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .whitelabel-container {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .ip-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
.w-100{
    width: 100%;
}
</style>
@endsection

@section('content')
<div class="whitelabel-container">
    <div class="whitelabel-header">
        <h1>Congratulations On Your WhiteLabel Purchase!</h1>
        <p>You're now ready to launch your own branded AI comedy platform</p>
    </div>
    <img src="https://cdn.convertri.com/ae86ebe2-499f-11e6-829d-066a9bd5fb79%2F103a9277c0a16132cdb65e74bbc6a1d535399f2a%2FIntro%20IMG.png" alt="{{ config('app.name') }}" class="w-100">
    <div class="info-card">
        <h3>📋 Follow These Steps</h3>
        <ul class="steps-list">
            <li>
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Prepare Your Domain</h4>
                    <p>Decide on your domain name (e.g., <code>yourbrand.com</code> or <code>app.yourbrand.com</code>)</p>
                </div>
            </li>
            <li>
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Prepare Your Logo</h4>
                    <p>If you have a logo, keep it ready. If not, we'll create one for you.</p>
                </div>
            </li>
            <li>
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Point Your Domain to Our Server</h4>
                    <p>Create an <strong>A record</strong> in your DNS settings:</p>
                    <div class="ip-row">
                        <span class="ip-code">62.146.182.136</span>
                        <button class="copy-ip-btn" onclick="copyIP()">
                            <i class="fas fa-copy"></i> Copy IP
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <div class="support-section">
        <a href="https://aistandup.tawk.help/" class="support-link" target="_blank">
            <i class="fas fa-headset"></i> Contact Support
        </a>
        <p class="support-text">After completing the steps above, contact support to submit your domain and logo.</p>
    </div>
</div>
@endsection

@section('js')
<script>
function copyIP() {
    const ip = '62.146.182.136';
    navigator.clipboard.writeText(ip);
    
    Swal.fire({
        title: 'Copied!',
        text: 'IP Address copied to clipboard',
        icon: 'success',
        confirmButtonColor: '#e65856',
        background: '#121212',
        color: '#ffffff',
        timer: 1500,
        showConfirmButton: false
    });
}
</script>
@endsection