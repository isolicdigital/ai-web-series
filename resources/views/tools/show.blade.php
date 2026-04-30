@extends('layouts.app')

@section('title', $tool->title)

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-6xl">

        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 mb-5 shadow-lg shadow-purple-500/25">
                <i class="fas {{ $tool->icon ?? 'fa-blog' }} text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent mb-3">
                {{ $tool->title }}
            </h1>
            <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                {{ $tool->description }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[400px_1fr] gap-8">
            
            <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-2xl p-6 sticky top-8">
                
                <form id="generateForm">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-white mb-2">Project Name</label>
                        <input type="text" name="title" id="projectTitle" class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500" placeholder="Enter project name" required>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-white mb-2">What would you like to create?</label>
                        <textarea name="prompt" id="promptText" class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 resize-vertical min-h-[120px]" placeholder="Describe what you want to generate..." required></textarea>
                        <div class="text-right text-xs text-gray-500 mt-1"><span id="charCount">0</span>/4000</div>
                    </div>

                    <div class="my-6 space-y-3">
                        <div id="toneTrigger" class="flex items-center justify-between p-3 bg-black/50 border border-purple-500/20 rounded-xl cursor-pointer hover:border-purple-500">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-volume-up text-purple-500"></i>
                                <span class="text-white text-sm">Tone</span>
                            </div>
                            <div class="flex items-center gap-2 text-purple-500 text-xs">
                                <span id="toneValue">Professional</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </div>
                        </div>
                        <input type="hidden" name="tone" id="toneInput" value="professional">

                        <div id="formatTrigger" class="flex items-center justify-between p-3 bg-black/50 border border-purple-500/20 rounded-xl cursor-pointer hover:border-purple-500">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-align-left text-purple-500"></i>
                                <span class="text-white text-sm">Format</span>
                            </div>
                            <div class="flex items-center gap-2 text-purple-500 text-xs">
                                <span id="formatValue">Paragraph</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </div>
                        </div>
                        <input type="hidden" name="format" id="formatInput" value="paragraph">
                    </div>

                    <!-- Creativity Slider - FIXED STYLING -->
                    <div class="mb-5">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-400">Creativity Level</span>
                            <span class="text-sm text-purple-500 font-medium" id="creativityValue">Balanced</span>
                        </div>
                        <input type="range" name="creativity" id="creativitySlider" min="1" max="5" value="3" style="width: 100%; height: 4px; -webkit-appearance: none; background: #d8b4fe; border-radius: 2px;">
                        <style>
                            #creativitySlider::-webkit-slider-thumb {
                                -webkit-appearance: none;
                                width: 16px;
                                height: 16px;
                                background: #a855f7;
                                border-radius: 50%;
                                cursor: pointer;
                                border: 2px solid white;
                            }
                            #lengthSlider::-webkit-slider-thumb {
                                -webkit-appearance: none;
                                width: 16px;
                                height: 16px;
                                background: #a855f7;
                                border-radius: 50%;
                                cursor: pointer;
                                border: 2px solid white;
                            }
                        </style>
                        <div class="flex justify-between mt-2 text-xs text-gray-500">
                            <span>Precise</span>
                            <span>Balanced</span>
                            <span>Creative</span>
                        </div>
                    </div>

                    <!-- Length Slider - FIXED STYLING -->
                    <div class="mb-5">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-400">Content Length</span>
                            <span class="text-sm text-purple-500 font-medium" id="lengthValue">Medium</span>
                        </div>
                        <input type="range" name="length" id="lengthSlider" min="1" max="5" value="3" style="width: 100%; height: 4px; -webkit-appearance: none; background: #d8b4fe; border-radius: 2px;">
                        <div class="flex justify-between mt-2 text-xs text-gray-500">
                            <span>Short</span>
                            <span>Medium</span>
                            <span>Long</span>
                        </div>
                    </div>

                    <div id="formError" class="bg-red-500/10 border border-red-500/25 rounded-xl p-3 text-red-400 text-sm hidden mb-4"></div>

                    <button type="submit" id="generateBtn" class="w-full py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold text-base rounded-full flex items-center justify-center gap-2 transition-all">
                        <i class="fas fa-magic"></i> Generate Content
                    </button>
                </form>
            </div>

            <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-purple-500/20 flex justify-between items-center">
                    <!-- Header States -->
                    <div>
                        <div id="headerIdle" class="flex items-center gap-2 font-semibold text-sm text-white">
                            <i class="fas fa-robot text-purple-500"></i>
                            <span>Generated Output</span>
                        </div>
                        <div id="headerGenerating" class="flex items-center gap-2 font-semibold text-sm text-purple-400 hidden">
                            <i class="fas fa-circle-notch animate-spin"></i>
                            <span>Generating Output...</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <!-- Dropdown Container -->
                        <div class="relative inline-block text-left" id="downloadDropdownContainer">
                            <!-- ADD 'disabled' HERE -->
                            <button type="button" id="downloadTriggerBtn" disabled class="flex items-center gap-1.5 bg-black/50 border border-purple-500/20 rounded-lg px-3 py-1.5 text-gray-400 text-xs hover:border-purple-500 hover:text-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-purple-500/20 disabled:hover:text-gray-400">
                                <i class="fas fa-download"></i> Download <i class="fas fa-chevron-down text-[8px] ml-0.5"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="downloadMenu" class="absolute right-0 mt-2 w-36 rounded-xl bg-slate-900 border border-purple-500/20 shadow-lg shadow-purple-500/10 hidden z-50 overflow-hidden">
                                <a href="#" class="download-option flex items-center px-4 py-2 text-xs text-gray-300 hover:bg-purple-500/10 hover:text-white transition-colors" data-type="txt">
                                    <i class="fas fa-file-alt w-5 text-gray-400"></i> Text (.txt)
                                </a>
                                <a href="#" class="download-option flex items-center px-4 py-2 text-xs text-gray-300 hover:bg-purple-500/10 hover:text-white transition-colors" data-type="doc">
                                    <i class="fas fa-file-word w-5 text-blue-400"></i> Word (.doc)
                                </a>
                                <a href="#" class="download-option flex items-center px-4 py-2 text-xs text-gray-300 hover:bg-purple-500/10 hover:text-white transition-colors" data-type="pdf">
                                    <i class="fas fa-file-pdf w-5 text-red-400"></i> PDF (.pdf)
                                </a>
                            </div>
                        </div>

                        <!-- ADD 'disabled' HERE -->
                        <button type="button" id="copyBtn" disabled class="bg-black/50 border border-purple-500/20 rounded-lg px-3 py-1.5 text-gray-400 text-xs hover:border-purple-500 hover:text-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-purple-500/20 disabled:hover:text-gray-400">Copy</button>
                        
                        <!-- ADD 'disabled' HERE -->
                        <button type="button" id="clearBtn" disabled class="bg-black/50 border border-purple-500/20 rounded-lg px-3 py-1.5 text-gray-400 text-xs hover:border-purple-500 hover:text-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-purple-500/20 disabled:hover:text-gray-400">Clear</button>
                    </div>
                </div>
                
                <div class="p-6 min-h-[500px] max-h-[600px] overflow-y-auto text-white/90 text-sm leading-relaxed" id="outputContainer">
                    <div id="loadingState" class="flex flex-col items-center justify-center min-h-[400px] gap-4 hidden">
                        <i class="fas fa-circle-notch text-4xl text-purple-500 animate-spin"></i>
                        <span class="text-gray-400">Generating...</span>
                    </div>
                    <div id="generatedText"></div>
                    <div id="emptyState" class="flex flex-col items-center justify-center min-h-[400px] gap-4">
                        <i class="fas fa-feather-alt text-4xl text-gray-600"></i>
                        <span class="text-gray-500">Your generated content will appear here</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Generations Table -->
        @if($generations->isNotEmpty())
        <div class="mt-8 bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-2xl overflow-hidden shadow-lg shadow-purple-500/5">
            <div class="px-6 py-4 border-b border-purple-500/20 bg-black/20">
                <h3 class="text-white text-lg font-semibold flex items-center gap-2">
                    <i class="fas fa-history text-purple-500"></i> Generation History
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-400">
                    <thead class="text-xs text-gray-300 uppercase bg-black/40 border-b border-purple-500/20">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Project Title</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Prompt Snippet</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Settings</th>
                            <th scope="col" class="px-6 py-4 font-semibold whitespace-nowrap">Date</th>
                            <th scope="col" class="px-6 py-4 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-500/10">
                        @foreach($generations as $gen)
                        <tr class="hover:bg-purple-500/5 transition-colors group">
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ $gen->title }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">
                                {{ $gen->prompt }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-purple-500/10 text-purple-400 text-[10px] px-2 py-1 rounded-md uppercase tracking-wider">
                                    <i class="fas fa-volume-up"></i> {{ $gen->tone ?? 'N/A' }}
                                </span>
                                <span class="inline-flex items-center gap-1 bg-pink-500/10 text-pink-400 text-[10px] px-2 py-1 rounded-md uppercase tracking-wider ml-1">
                                    <i class="fas fa-align-left"></i> {{ $gen->format ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                {{ $gen->created_at->format('M d, Y \a\t h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" class="history-btn px-4 py-2 bg-black/50 border border-purple-500/30 text-purple-400 hover:bg-purple-600 hover:text-white hover:border-purple-600 rounded-xl transition-all text-xs font-medium" data-id="{{ $gen->id }}">
                                    Load Output
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Tone Modal -->
<div id="toneModal" class="fixed inset-0 bg-black/80 z-[1000] hidden items-center justify-center">
    <div class="bg-slate-900 border border-purple-500/20 rounded-2xl max-w-md w-[90%]">
        <div class="p-5 border-b border-purple-500/20">
            <h3 class="text-xl font-semibold text-white">Select Tone</h3>
        </div>
        <div class="p-5">
            <div class="tone-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-tone="professional">
                <div class="flex items-center gap-3">
                    <i class="fas fa-briefcase text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Professional</div>
                        <div class="text-gray-400 text-xs">Business-appropriate and formal</div>
                    </div>
                </div>
            </div>
            <div class="tone-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-tone="casual">
                <div class="flex items-center gap-3">
                    <i class="fas fa-comment text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Casual</div>
                        <div class="text-gray-400 text-xs">Conversational and friendly</div>
                    </div>
                </div>
            </div>
            <div class="tone-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-tone="persuasive">
                <div class="flex items-center gap-3">
                    <i class="fas fa-bullhorn text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Persuasive</div>
                        <div class="text-gray-400 text-xs">Convincing and influential</div>
                    </div>
                </div>
            </div>
            <div class="tone-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-tone="informative">
                <div class="flex items-center gap-3">
                    <i class="fas fa-info-circle text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Informative</div>
                        <div class="text-gray-400 text-xs">Educational and explanatory</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-purple-500/20 text-right">
            <button id="closeToneModal" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 rounded-full text-white text-sm">Close</button>
        </div>
    </div>
</div>

<!-- Format Modal -->
<div id="formatModal" class="fixed inset-0 bg-black/80 z-[1000] hidden items-center justify-center">
    <div class="bg-slate-900 border border-purple-500/20 rounded-2xl max-w-md w-[90%]">
        <div class="p-5 border-b border-purple-500/20">
            <h3 class="text-xl font-semibold text-white">Select Format</h3>
        </div>
        <div class="p-5">
            <div class="format-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-format="paragraph">
                <div class="flex items-center gap-3">
                    <i class="fas fa-align-left text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Paragraph</div>
                        <div class="text-gray-400 text-xs">Traditional text format</div>
                    </div>
                </div>
            </div>
            <div class="format-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-format="bullet">
                <div class="flex items-center gap-3">
                    <i class="fas fa-list text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Bullet Points</div>
                        <div class="text-gray-400 text-xs">List format for easy reading</div>
                    </div>
                </div>
            </div>
            <div class="format-option cursor-pointer p-3 rounded-xl hover:bg-purple-500/10" data-format="structured">
                <div class="flex items-center gap-3">
                    <i class="fas fa-layer-group text-purple-500"></i>
                    <div>
                        <div class="text-white font-medium">Structured</div>
                        <div class="text-gray-400 text-xs">Organized with headings and sections</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-purple-500/20 text-right">
            <button id="closeFormatModal" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 rounded-full text-white text-sm">Close</button>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    /* Restore Spacing for Markdown Tags (Overrides Tailwind Preflight) */
    #generatedText {
        line-height: 1.7;
    }
    #generatedText p {
        margin-bottom: 1.25rem;
    }
    #generatedText p:last-child {
        margin-bottom: 0;
    }
    #generatedText h1, 
    #generatedText h2, 
    #generatedText h3, 
    #generatedText h4 {
        color: #ffffff;
        font-weight: bold;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    #generatedText h1 { font-size: 1.5rem; }
    #generatedText h2 { font-size: 1.25rem; }
    #generatedText h3 { font-size: 1.125rem; }
    
    #generatedText ul, 
    #generatedText ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }
    #generatedText ul { list-style-type: disc; }
    #generatedText ol { list-style-type: decimal; }
    #generatedText li { margin-bottom: 0.5rem; }
    
    #generatedText strong {
        color: #d8b4fe; /* Purple to match your UI */
        font-weight: 600;
    }

    /* Blinking Cursor Effect */
    @keyframes blink { 
        0%, 100% { opacity: 1; }
        50% { opacity: 0; } 
    }
    .is-typing::after {
        content: '▋';
        display: inline-block;
        vertical-align: bottom;
        animation: blink 1s step-end infinite;
        color: #a855f7; /* Tailwind purple-500 to match your theme */
        margin-left: 4px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // SIMPLE, DIRECT EVENT BINDING - NO COMPLEXITY
    document.addEventListener('DOMContentLoaded', function() {

        const headerIdle = document.getElementById('headerIdle');
        const headerGenerating = document.getElementById('headerGenerating');
        const copyBtn = document.getElementById('copyBtn');
        const clearBtn = document.getElementById('clearBtn');

        // Inject server-side history into JS safely
        const userHistory = @json($generations);

        // Table Button Click Handler
        document.querySelectorAll('.history-btn').forEach(btn => {
            btn.onclick = function() {
                const genId = parseInt(this.getAttribute('data-id'));
                const generation = userHistory.find(g => g.id === genId);
                
                if(generation) {
                    // Populate the form fields
                    document.getElementById('projectTitle').value = generation.title;
                    document.getElementById('promptText').value = generation.prompt;
                    document.getElementById('charCount').innerText = generation.prompt.length;
                    
                    // Show Output
                    document.getElementById('emptyState').style.display = 'none';
                    document.getElementById('loadingState').style.display = 'none';
                    
                    // Render historical markdown
                    const generatedText = document.getElementById('generatedText');
                    generatedText.innerHTML = marked.parse(generation.output);
                    
                    // NEW: Enable buttons because history was loaded
                    document.getElementById('copyBtn').disabled = false;
                    document.getElementById('clearBtn').disabled = false;
                    document.getElementById('downloadTriggerBtn').disabled = false;
                    
                    // Scroll up to the output container smoothly
                    const outputContainer = document.getElementById('outputContainer');
                    outputContainer.scrollTop = 0;
                    outputContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            };
        });

        // 10. Download Dropdown & Action Logic
        const downloadTriggerBtn = document.getElementById('downloadTriggerBtn');
        const downloadMenu = document.getElementById('downloadMenu');

        // Toggle dropdown
        downloadTriggerBtn.onclick = function() {
            downloadMenu.classList.toggle('hidden');
        };

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!downloadTriggerBtn.contains(e.target) && !downloadMenu.contains(e.target)) {
                downloadMenu.classList.add('hidden');
            }
        });

        // Handle specific download clicks
        document.querySelectorAll('.download-option').forEach(option => {
            option.onclick = function(e) {
                e.preventDefault();
                downloadMenu.classList.add('hidden'); // Hide menu after click
                
                const type = this.getAttribute('data-type');
                const titleInput = document.getElementById('projectTitle').value.trim();
                // Create a clean filename from the project title, fallback to 'ai-generation'
                const safeName = (titleInput || 'ai-generation').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                const filename = `${safeName}.${type}`;
                
                const plainText = generatedText.innerText;
                const htmlText = generatedText.innerHTML;

                if (!plainText) return; // Do nothing if there's no output

                if (type === 'txt') {
                    // Standard Text Download
                    downloadFile(plainText, filename, 'text/plain');
                } 
                else if (type === 'doc') {
                    // Word Document Wrapper Trick
                    const header = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Export HTML to Word</title></head><body>";
                    const footer = "</body></html>";
                    const sourceHTML = header + htmlText + footer;
                    downloadFile(sourceHTML, filename, 'application/msword');
                } 
                else if (type === 'pdf') {
                    // Configure PDF styles (forcing black text so it's readable on white PDF paper)
                    const element = document.createElement('div');
                    element.innerHTML = htmlText;
                    element.style.padding = '20px';
                    element.style.fontFamily = 'Arial, sans-serif';
                    element.style.lineHeight = '1.6';
                    element.style.color = '#000000'; 
                    
                    // Inject print-friendly styling for the PDF
                    const style = document.createElement('style');
                    style.innerHTML = `
                        h1, h2, h3, h4 { font-weight: bold; margin-top: 15px; margin-bottom: 10px; }
                        h1 { font-size: 24px; } h2 { font-size: 20px; } h3 { font-size: 16px; }
                        p { margin-bottom: 12px; }
                        ul { list-style-type: disc; margin-left: 20px; margin-bottom: 12px; }
                        ol { list-style-type: decimal; margin-left: 20px; margin-bottom: 12px; }
                        strong { font-weight: bold; }
                    `;
                    element.prepend(style);

                    const opt = {
                        margin:       0.5,
                        filename:     filename,
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 },
                        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                    };

                    // Change button to indicate loading
                    const originalBtnHtml = downloadTriggerBtn.innerHTML;
                    downloadTriggerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                    
                    html2pdf().set(opt).from(element).save().then(() => {
                        downloadTriggerBtn.innerHTML = originalBtnHtml; // Restore button
                    });
                }
            };
        });

        // Helper function to trigger browser download
        function downloadFile(content, filename, contentType) {
            const blob = new Blob([content], { type: contentType });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
        
        // 1. CREATIVITY SLIDER
        const creativitySlider = document.getElementById('creativitySlider');
        const creativityValue = document.getElementById('creativityValue');
        const creativityMap = ['Very Precise', 'Precise', 'Balanced', 'Creative', 'Very Creative'];
        
        creativitySlider.oninput = function() {
            creativityValue.innerText = creativityMap[this.value - 1];
        };
        
        // 2. LENGTH SLIDER
        const lengthSlider = document.getElementById('lengthSlider');
        const lengthValue = document.getElementById('lengthValue');
        const lengthMap = ['Very Short', 'Short', 'Medium', 'Long', 'Very Long'];
        
        lengthSlider.oninput = function() {
            lengthValue.innerText = lengthMap[this.value - 1];
        };
        
        // 3. TONE MODAL
        const toneTrigger = document.getElementById('toneTrigger');
        const toneModal = document.getElementById('toneModal');
        const toneInput = document.getElementById('toneInput');
        const toneValueSpan = document.getElementById('toneValue');
        
        toneTrigger.onclick = function() {
            toneModal.style.display = 'flex';
        };
        
        document.querySelectorAll('.tone-option').forEach(function(opt) {
            opt.onclick = function() {
                const tone = this.getAttribute('data-tone');
                toneInput.value = tone;
                toneValueSpan.innerText = tone.charAt(0).toUpperCase() + tone.slice(1);
                toneModal.style.display = 'none';
            };
        });
        
        document.getElementById('closeToneModal').onclick = function() {
            toneModal.style.display = 'none';
        };
        
        // 4. FORMAT MODAL
        const formatTrigger = document.getElementById('formatTrigger');
        const formatModal = document.getElementById('formatModal');
        const formatInput = document.getElementById('formatInput');
        const formatValueSpan = document.getElementById('formatValue');
        
        formatTrigger.onclick = function() {
            formatModal.style.display = 'flex';
        };
        
        document.querySelectorAll('.format-option').forEach(function(opt) {
            opt.onclick = function() {
                const format = this.getAttribute('data-format');
                formatInput.value = format;
                formatValueSpan.innerText = format.charAt(0).toUpperCase() + format.slice(1);
                formatModal.style.display = 'none';
            };
        });
        
        document.getElementById('closeFormatModal').onclick = function() {
            formatModal.style.display = 'none';
        };
        
        // 5. Close modals when clicking outside
        window.onclick = function(e) {
            if (e.target === toneModal) toneModal.style.display = 'none';
            if (e.target === formatModal) formatModal.style.display = 'none';
        };
        
        // 6. Character counter
        const promptText = document.getElementById('promptText');
        const charCount = document.getElementById('charCount');
        promptText.oninput = function() {
            charCount.innerText = this.value.length;
        };
        
        // 7. Form submission
        const form = document.getElementById('generateForm');
        const generateBtn = document.getElementById('generateBtn');
        const originalGenerateBtnHTML = generateBtn.innerHTML;
        const loadingState = document.getElementById('loadingState');
        const generatedText = document.getElementById('generatedText');
        const emptyState = document.getElementById('emptyState');
        const outputContainer = document.getElementById('outputContainer');
        
        marked.setOptions({
            breaks: true,
            gfm: true
        });

        form.onsubmit = async function(e) {
            e.preventDefault();
            
            // 1. Gather Inputs
            const title = document.getElementById('projectTitle').value.trim();
            const prompt = promptText.value.trim();
            
            // 2. Validation
            if (!title || !prompt) {
                const errorDiv = document.getElementById('formError');
                errorDiv.innerText = 'Please fill in all required fields.';
                errorDiv.classList.remove('hidden');
                setTimeout(function() { errorDiv.classList.add('hidden'); }, 5000);
                return;
            }
            
            // 3. LOCK UI & SHOW GENERATING STATES
            // Main Generate Button
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="fas fa-circle-notch animate-spin"></i> Generating...';
            generateBtn.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Header & Action Buttons
            headerIdle.classList.add('hidden');
            headerGenerating.classList.remove('hidden');
            copyBtn.disabled = true;
            clearBtn.disabled = true;
            document.getElementById('downloadTriggerBtn').disabled = true; // <-- Add this
            
            // Output Container
            loadingState.style.display = 'flex';
            emptyState.style.display = 'none';
            generatedText.innerHTML = '';
            generatedText.classList.add('is-typing'); // Triggers the blinking cursor
            
            // 4. Prepare Request Data
            const params = new URLSearchParams();
            params.append('title', title);
            params.append('prompt', prompt);
            params.append('tone', toneInput.value);
            params.append('format', formatInput.value);
            params.append('creativity', creativitySlider.value);
            params.append('length', lengthSlider.value);
            params.append('_token', document.querySelector('input[name="_token"]').value);
            
            try {
                // 5. Fire Request
                const response = await fetch('{{ route("contents.generate", $tool->slug) }}?' + params.toString(), {
                    method: 'GET',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/event-stream' 
                    }
                });
                
                if (!response.ok) throw new Error('Request failed');
                
                // 6. Setup Stream Reader
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let fullText = '';
                let buffer = '';
                
                // Hide the big central spinner once the stream actually connects
                loadingState.style.display = 'none'; 
                
                // 7. Process Stream
                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;
                    
                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';
                    
                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.slice(6);
                            
                            if (data === '[DONE]') continue;
                            
                            try {
                                const parsed = JSON.parse(data);
                                if (parsed.content) {
                                    fullText += parsed.content;
                                    
                                    // Parse Markdown to HTML in real-time
                                    generatedText.innerHTML = marked.parse(fullText);
                                    
                                    // Auto-scroll to bottom as text arrives
                                    outputContainer.scrollTop = outputContainer.scrollHeight;
                                }
                            } catch(e) {
                                // Silently ignore incomplete JSON chunks
                            }
                        }
                    }
                }
                
                // Fallback if the AI returns literally nothing
                if (!fullText) emptyState.style.display = 'flex';
                
            } catch (error) {
                console.error("Streaming error:", error);
                loadingState.style.display = 'none';
                emptyState.style.display = 'flex';
                
                const errorDiv = document.getElementById('formError');
                errorDiv.innerText = 'Connection lost. Please try generating again.';
                errorDiv.classList.remove('hidden');
                setTimeout(function() { errorDiv.classList.add('hidden'); }, 5000);
                
            } finally {
                // 8. UNLOCK UI & RESTORE IDLE STATES
                
                // Main Generate Button
                generateBtn.disabled = false;
                generateBtn.innerHTML = originalGenerateBtnHTML;
                generateBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                
                // Header States
                headerGenerating.classList.add('hidden');
                headerIdle.classList.remove('hidden');
                
                // Remove blinking cursor
                generatedText.classList.remove('is-typing'); 
                
                // CONDITIONAL BUTTON ENABLING
                const hasText = generatedText.innerText.trim().length > 0;
                copyBtn.disabled = !hasText;
                clearBtn.disabled = !hasText;
                document.getElementById('downloadTriggerBtn').disabled = !hasText;
            }
        };
        
        // 8. Enhanced Copy button (Supports Rich Text / Formatting)
        document.getElementById('copyBtn').onclick = async function() {
            const plainText = generatedText.innerText;
            const htmlText = generatedText.innerHTML;
            
            if (!plainText) return;

            // Save the original button state
            const originalContent = this.innerHTML;
            
            // Change button to show success state
            this.innerHTML = '<i class="fas fa-check text-green-500"></i> <span class="text-green-500">Copied!</span>';
            this.classList.add('border-green-500/50', 'bg-green-500/10');

            try {
                // Modern Clipboard API: Writes both plain text and HTML
                if (navigator.clipboard && window.ClipboardItem) {
                    const textBlob = new Blob([plainText], { type: 'text/plain' });
                    const htmlBlob = new Blob([htmlText], { type: 'text/html' });
                    
                    const clipboardItem = new ClipboardItem({
                        'text/plain': textBlob,
                        'text/html': htmlBlob
                    });
                    
                    await navigator.clipboard.write([clipboardItem]);
                } else {
                    // Fallback for older browsers
                    await navigator.clipboard.writeText(plainText);
                }
            } catch (err) {
                console.error('Failed to copy text: ', err);
                this.innerHTML = '<i class="fas fa-times text-red-500"></i> Error';
            }

            // Reset button after 2 seconds
            setTimeout(() => { 
                this.innerHTML = originalContent; 
                this.classList.remove('border-green-500/50', 'bg-green-500/10');
            }, 2000);
        };
        
        // 9. Clear button
        document.getElementById('clearBtn').onclick = function() {
            generatedText.innerHTML = '';
            emptyState.style.display = 'flex';
            
            // Disable the buttons since the container is now empty
            copyBtn.disabled = true;
            document.getElementById('downloadTriggerBtn').disabled = true;
            this.disabled = true; // Disables the clear button itself
        };
        
    });
</script>
@endsection