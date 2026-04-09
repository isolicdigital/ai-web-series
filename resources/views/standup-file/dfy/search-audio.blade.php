@extends('layouts.app')
@section('css')
    <!-- Sweet Alert CSS -->
    <link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
    
    <style>
        .nav-link{ color: #A715FF !important; }
        
        /* Audio Container Styles - Dark Theme Compatible with Fixed Margins */
        #audio-containers-wrapper {
            margin: 0 -10px;
            padding: 0 10px;
        }
        
        .audio-container {
            margin-bottom: 25px;
            transition: transform 0.3s ease;
            padding: 0 10px;
        }
        
        .audio-container:hover {
            transform: translateY(-5px);
        }
        
        .grid-item {
            background: var(--card-bg, #ffffff);
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--card-shadow, rgba(0,0,0,0.1));
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--card-border, #e9ecef);
            margin: 0;
        }
        
        .grid-item:hover {
            box-shadow: 0 8px 25px var(--card-shadow-hover, rgba(0,0,0,0.15));
        }
        
        .grid-audio-wrapper {
            position: relative;
            height: 150px;
            background: linear-gradient(135deg, var(--gradient-start, #0783CF) 0%, var(--gradient-end, #01C3DF) 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .audio-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--gradient-start, #0783CF) 0%, var(--gradient-end, #01C3DF) 100%);
            color: var(--text-inverse, #ffffff);
        }
        
        .audio-placeholder i {
            font-size: 48px;
            opacity: 0.8;
        }
        
        .audio-processing {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: var(--overlay-bg, rgba(0,0,0,0.7));
            color: var(--text-inverse, #ffffff);
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .grid-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 20;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            gap: 5px;
        }
        
        .grid-audio-wrapper:hover .grid-buttons {
            opacity: 1;
        }
        
        .grid-audio-view {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: var(--button-bg, rgba(255,255,255,0.9));
            color: var(--button-text, #333333);
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px var(--button-shadow, rgba(0,0,0,0.2));
            backdrop-filter: blur(10px);
        }
        
        .grid-audio-view:hover {
            background: var(--button-bg-hover, #ffffff);
            color: var(--primary-color, #A715FF);
            transform: scale(1.1);
        }
        
        .grid-description {
            padding: 15px;
            background: var(--card-bg, #ffffff);
        }
        
        .audio-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary, #333333);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .audio-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        
        .audio-vendor {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary-color, #A715FF);
            background: var(--badge-bg, rgba(167, 21, 255, 0.1));
            padding: 3px 8px;
            border-radius: 12px;
            border: 1px solid var(--badge-border, rgba(167, 21, 255, 0.2));
        }
        
        .audio-status {
            font-size: 11px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 12px;
            border: 1px solid transparent;
        }
        
        .status-completed {
            background: var(--success-bg, rgba(40, 167, 69, 0.1));
            color: var(--success-color, #28a745);
            border-color: var(--success-border, rgba(40, 167, 69, 0.2));
        }
        
        .status-processing {
            background: var(--warning-bg, rgba(255, 193, 7, 0.1));
            color: var(--warning-color, #ffc107);
            border-color: var(--warning-border, rgba(255, 193, 7, 0.2));
        }
        
        .status-queued {
            background: var(--secondary-bg, rgba(108, 117, 125, 0.1));
            color: var(--secondary-color, #6c757d);
            border-color: var(--secondary-border, rgba(108, 117, 125, 0.2));
        }
        
        .status-failed {
            background: var(--danger-bg, rgba(220, 53, 69, 0.1));
            color: var(--danger-color, #dc3545);
            border-color: var(--danger-border, rgba(220, 53, 69, 0.2));
        }
        
        .audio-date small {
            color: var(--text-muted, #6c757d) !important;
        }
        
        /* Empty state */
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }
        
        .empty-state i {
            color: var(--text-muted, #6c757d);
        }
        
        .empty-state h5 {
            color: var(--text-muted, #6c757d);
        }
        
        .empty-state p {
            color: var(--text-muted, #6c757d);
        }
        
        /* Responsive spacing */
        @media (min-width: 576px) {
            #audio-containers-wrapper {
                margin: 0 -15px;
                padding: 0 15px;
            }
            
            .audio-container {
                padding: 0 15px;
            }
        }
        
        @media (min-width: 768px) {
            #audio-containers-wrapper {
                margin: 0 -20px;
                padding: 0 20px;
            }
            
            .audio-container {
                padding: 0 20px;
            }
        }
        
        /* Dark theme variables */
        [data-theme="dark"] {
            --card-bg: #000000;
            --card-border: #4a5568;
            --card-shadow: rgba(0, 0, 0, 0.3);
            --card-shadow-hover: rgba(0, 0, 0, 0.4);
            --text-primary: #e2e8f0;
            --text-muted: #a0aec0;
            --text-inverse: #ffffff;
            --gradient-start: #4c51bf;
            --gradient-end: #7e3af2;
            --button-bg: rgba(255, 255, 255, 0.15);
            --button-text: #ffffff;
            --button-bg-hover: rgba(255, 255, 255, 0.25);
            --button-shadow: rgba(0, 0, 0, 0.3);
            --overlay-bg: rgba(0, 0, 0, 0.8);
            --badge-bg: rgba(167, 21, 255, 0.2);
            --badge-border: rgba(167, 21, 255, 0.3);
            --primary-color: #a78bfa;
            --success-bg: rgba(72, 187, 120, 0.2);
            --success-color: #48bb78;
            --success-border: rgba(72, 187, 120, 0.3);
            --warning-bg: rgba(237, 137, 54, 0.2);
            --warning-color: #01C3DF;
            --warning-border: rgba(237, 137, 54, 0.3);
            --danger-bg: rgba(245, 101, 101, 0.2);
            --danger-color: #f56565;
            --danger-border: rgba(245, 101, 101, 0.3);
            --secondary-bg: rgba(160, 174, 192, 0.2);
            --secondary-color: #a0aec0;
            --secondary-border: rgba(160, 174, 192, 0.3);
        }
        
        /* System dark mode detection */
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --card-bg: #000000;
                --card-border: #4a5568;
                --card-shadow: rgba(0, 0, 0, 0.3);
                --card-shadow-hover: rgba(0, 0, 0, 0.4);
                --text-primary: #e2e8f0;
                --text-muted: #a0aec0;
                --text-inverse: #ffffff;
                --gradient-start: #4c51bf;
                --gradient-end: #7e3af2;
                --button-bg: rgba(255, 255, 255, 0.15);
                --button-text: #ffffff;
                --button-bg-hover: rgba(255, 255, 255, 0.25);
                --button-shadow: rgba(0, 0, 0, 0.3);
                --overlay-bg: rgba(0, 0, 0, 0.8);
                --badge-bg: rgba(167, 21, 255, 0.2);
                --badge-border: rgba(167, 21, 255, 0.3);
                --primary-color: #a78bfa;
                --success-bg: rgba(72, 187, 120, 0.2);
                --success-color: #48bb78;
                --success-border: rgba(72, 187, 120, 0.3);
                --warning-bg: rgba(237, 137, 54, 0.2);
                --warning-color: #01C3DF;
                --warning-border: rgba(237, 137, 54, 0.3);
                --danger-bg: rgba(245, 101, 101, 0.2);
                --danger-color: #f56565;
                --danger-border: rgba(245, 101, 101, 0.3);
                --secondary-bg: rgba(160, 174, 192, 0.2);
                --secondary-color: #a0aec0;
                --secondary-border: rgba(160, 174, 192, 0.3);
            }
        }
        
        /* Loading animation */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .loading {
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Audio player styling */
        .audio-player {
            width: 100%;
            margin-top: 10px;
        }
        
        .audio-player audio {
            width: 100%;
            height: 40px;
            border-radius: 20px;
        }
        
        /* Form styling to match sample */
        .photo-studio-tools {
            margin-bottom: 1.5rem;
        }
        
        .input-box {
            margin-bottom: 1.5rem;
        }
        
        .input-box h6 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border: 1px solid var(--card-border, #e9ecef);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background: var(--card-bg, #ffffff);
            color: var(--text-primary, #333333);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color, #A715FF);
            box-shadow: 0 0 0 0.2rem rgba(167, 21, 255, 0.25);
        }
        
        .main-action-button {
            background: linear-gradient(135deg, var(--primary-color, #A715FF) 0%, #7e3af2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .main-action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(167, 21, 255, 0.4);
        }
        
        .card {
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--card-border, #e9ecef);
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--card-shadow, rgba(0,0,0,0.1));
        }
        
        .card-title {
            color: var(--text-primary, #333333);
            font-weight: 700;
        }
        
        .text-muted {
            color: var(--text-muted, #6c757d) !important;
        }
        
        /* Audio prompt wrapper styling */
        .audio-prompt-wrapper {
            background: var(--card-bg, #ffffff);
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--card-shadow, rgba(0,0,0,0.1));
            margin-bottom: 2rem;
        }
        
        .audio-prompt {
            display: flex;
            align-items: center;
            /* gap: 15px; */
        }
        
        .audio-prompt .input-box {
            flex: 1;
            margin-bottom: 0;
        }
        
        .audio-prompt .form-group {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .audio-prompt {
                flex-direction: column;
                gap: 10px;
            }
            
            .audio-prompt .input-box {
                width: 100%;
            }
            
            .audio-prompt .btn {
                width: 100%;
            }
        }



.audio-prompt-wrapper {
    border-radius: 50vh;
}

.audio-prompt-wrapper .audio-prompt .input-box {
    width: 90%
}

.audio-prompt-wrapper .audio-prompt .input-box .form-control {
    height: 48px;
    line-height: 48px;
    padding: 0 20px;
    background-color: #fff!important;
    border-radius: 50vh;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-color: #f5f9fc
}

.audio-prompt-wrapper .audio-prompt .input-box .form-control:focus {
    border-color: #A715FF
}

.audio-prompt-wrapper .audio-prompt .negative {
    width: 100%
}

.audio-prompt-wrapper .audio-prompt .negative .form-control {
    border-radius: 50vh
}

.audio-prompt-wrapper .audio-prompt #audio-generate {
    height: 48px;
    font-size: 14px;
    text-transform: none;
    font-weight: 600;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    min-width: 170px;
    background: #000;
    border-color: #000
}

.audio-prompt-wrapper .audio-prompt #audio-generate:hover,.audio-prompt-wrapper .audio-prompt #audio-generate:focus {
    background: #A715FF;
    border-color: #A715FF;
    transform: none
}

.audio-prompt-wrapper .audio-prompt #audio-generate :disabled {
    background-color: green;
    cursor: not-allowed
}


body.dark-theme .audio-prompt #prompt {
    background-color: #313338!important;
    border-color: #313338
}
    </style>
@endsection

@section('content')

<div class="page-body mt-2 relative after:h-px after:w-full after:bg-[var(--tblr-body-bg)] after:absolute after:top-full after:left-0 after:-mt-px">
   <div class="lqd-page-content-container h-full container">
      <div class="py-10 px-10">

         <form id="openai-form" action="" method="get" class="mt-24">    
            @csrf    
            <div class="row" id="audio-side-space">
                <div class="row no-gutters justify-content-center">
                    <div class="col-lg-9 col-md-11 col-sm-12 text-center">
                        <h3 class="card-title mt-6 fs-20"><i class="fa-solid fa-music mr-2 text-primary"></i>{{ $page_title }}</h3>
                        <h6 class="text-muted mb-7">Elevate Your Films with AI-Composed Audio Tailored to Every Scene!</h6>
                        
                        <div class="card mb-4 border-0 audio-prompt-wrapper">
                            <div class="card-body p-0">                    
                                <div class="audio-prompt d-flex">
                                    <div class="input-box mb-0">                                
                                        <div class="form-group">                                    
                                            <input type="text" class="form-control" id="prompt" name="prompt" placeholder="Search for {{$purpose}}..." value="{{ isset($prompt) ? $prompt : '' }}" required>
                                        </div> 
                                    </div> 
                                    <div>
                                        <button type="submit" name="submit" class="btn btn-primary w-100 pt-2 pb-2" id="audio-generate">
                                            <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Generate
                                        </button>
                                    </div>
                                </div>                    
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="lqd-ai-audios-wrap mt-8" id="lqd-ai-audios-wrap">
                    <!-- <h3 class="mb-8 flex items-center gap-2">
                    <i class="fa-solid fa-music text-primary"></i> {{ $purpose }} Result 
                    </h3> -->
                    
                    <div class="row m-3" id="audio-containers-wrapper">
                    @if(sizeof($audios)==0)
                        <div class="col-12 text-center py-8">
                            <div class="empty-state">
                                <i class="fa-solid fa-music-slash fs-48 mb-3"></i>
                                <h5>{{ __('No audio generated yet') }}</h5>
                                <p>{{ __('Your generated audio tracks will appear here') }}</p>
                            </div>
                        </div>
                    @else
                        @foreach($audios as $index => $aud)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 audio-container">                
                            <div class="grid-item">
                                <div class="grid-audio-wrapper">
                                <div class="audio-placeholder">
                                    <i class="fa-solid fa-music"></i>
                                </div>
                                <div class="grid-buttons">
                                    <a href="{{$aud['url']}}" class="grid-audio-view" download title="{{ __('Download Audio') }}">
                                    <i class="fa-solid fa-download fs-14"></i>
                                    </a>
                                    <a href="#" class="grid-audio-view playAudio" data-url="{{$aud['url']}}" title="{{ __('Play Audio') }}">
                                    <i class="fa-solid fa-play fs-14"></i>
                                    </a>
                                </div>
                                </div>
                                
                                <div class="grid-description">
                                <div class="audio-title" title="{{ $aud['title'] }}">
                                    {{ Str::limit($aud['title'], 60) }}
                                </div>
                                
                                <div class="audio-meta">
                                    <span class="audio-vendor">
                                    AI Audio Generator
                                    </span>
                                    
                                    <span class="audio-status status-completed">
                                    {{ __('Ready') }}
                                    </span>
                                </div>
                                
                                <div class="audio-player hidden">
                                    <audio controls>
                                    <source src="{{$aud['url']}}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                    </audio>
                                </div>
                                
                                <div class="audio-date mt-2">
                                    <small class="text-muted">
                                    {{ \Carbon\Carbon::now()->format('M d, Y') }}
                                    </small>
                                </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                    </div>
                </div>
            </div>
         </form>

         <!-- Audio Player Modal -->
         <div class="modal fade" id="audio-player-modal" tabindex="-1" aria-labelledby="audioPlayerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e9ecef);">
                  <div class="modal-header" style="border-color: var(--card-border, #e9ecef);">
                     <h6 style="color: var(--text-primary, #333333);" id="audio-player-title">{{ __('Audio Preview') }}</h6>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--btn-close-filter, none);"></button>
                  </div>
                  <div class="modal-body pb-6 pr-5 pl-5 text-center" style="color: var(--text-primary, #333333);">
                     <audio id="modal-audio-player" controls class="w-100">
                        Your browser does not support the audio element.
                     </audio>
                  </div>
               </div>
            </div>
         </div>
         
      </div>
   </div>
</div>

@endsection

@section('js')
<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
<script type="text/javascript">
    // Theme detection and switching
    function detectTheme() {
        // Check if user has explicit theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            return;
        }
        
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    $(function () {
        "use strict";

        detectTheme();
        
        // Watch for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('theme')) {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });

        // SUBMIT FORM
        $('#openai-form').on('submit', function(e) {
            e.preventDefault();

            $('#audio-generate').html('<i class="fa-solid fa-wand-magic-sparkles fa-beat-fade mr-2"></i>{{ __("Generating...") }}');
            $('#audio-generate').prop('disabled', true);     
            
            // Show loading indicator
            $('#app-loading-indicator').removeClass('opacity-0');
            
            // Submit the form
            $(this).submit();
        });

        // Play Audio
        $(document).on('click', '.playAudio', function(e) {
            e.preventDefault();
            
            const audioUrl = $(this).data('url');
            const audioTitle = $(this).closest('.grid-item').find('.audio-title').text();
            
            $('#modal-audio-player').attr('src', audioUrl);
            $('#audio-player-title').text(audioTitle);
            
            var audioModal = new bootstrap.Modal(document.getElementById('audio-player-modal'));
            audioModal.show();
            
            // Play the audio when modal is shown
            $('#audio-player-modal').on('shown.bs.modal', function () {
                document.getElementById('modal-audio-player').play();
            });
            
            // Pause the audio when modal is hidden
            $('#audio-player-modal').on('hidden.bs.modal', function () {
                document.getElementById('modal-audio-player').pause();
            });
        });

        // Toggle audio player on card click
        $(document).on('click', '.grid-item', function(e) {
            // Don't trigger if user clicked on buttons
            if ($(e.target).closest('.grid-buttons').length === 0) {
                $(this).find('.audio-player').toggleClass('hidden');
                
                // If showing, play the audio
                if (!$(this).find('.audio-player').hasClass('hidden')) {
                    $(this).find('audio')[0].play();
                } else {
                    $(this).find('audio')[0].pause();
                }
            }
        });
    });
</script>
@endsection