<!doctype html>
<html>
<head>
    <title>{{ $title }} | Editor</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="{{ asset('custom/brand/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('builder/dist/builder.css') }}">
    <style>
        @keyframes pulse-bg {
            0% {
                background-color: #3490dc;
            }
            50% {
                background-color: #5fa8ec;
            }
            100% {
                background-color: #3490dc;
            }
        }

        .btn-saving {
            animation: pulse-bg 1s infinite;
            color: white !important;
        }
    </style>
    <script src="{{ asset('builder/dist/builder.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        window.CSRF_TOKEN='{{ csrf_token() }}';
        const saveUrl = "{{ route('page-builder.save', ['id' => $id, 'title' => base64_encode($title), 'cat' => $cat, 'dir' => $dir]) }}";
        const saveAssets = "{{ route('page-builder.assets', ['id' => $id]) }}";
        const backUrl = "{{ route('page-builder.saves') }}";
        switch (window.location.protocol) {
            case 'file:':
                alert('Please put the builder/ folder into your document root and open it through a web URL');
                window.location.href = "./index.html";
                break;
        }

        const editorParams = new URLSearchParams(window.location.search);
        const templates = @json(array_merge($layouts, $templateList));

        const tags = [
            {type: 'label', tag: '{CONTACT_FIRST_NAME}'},
            {type: 'label', tag: '{CONTACT_LAST_NAME}'},
            {type: 'label', tag: '{CONTACT_FULL_NAME}'},
            {type: 'label', tag: '{CONTACT_EMAIL}'},
            {type: 'label', tag: '{CONTACT_PHONE}'},
            {type: 'label', tag: '{CONTACT_ADDRESS}'},
            {type: 'label', tag: '{ORDER_ID}'},
            {type: 'label', tag: '{ORDER_DUE}'},
            {type: 'label', tag: '{ORDER_TAX}'},
            {type: 'label', tag: '{PRODUCT_NAME}'},
            {type: 'label', tag: '{PRODUCT_PRICE}'},
            {type: 'label', tag: '{PRODUCT_QTY}'},
            {type: 'label', tag: '{PRODUCT_SKU}'},
            {type: 'label', tag: '{AGENT_NAME}'},
            {type: 'label', tag: '{AGENT_SIGNATURE}'},
            {type: 'label', tag: '{AGENT_MOBILE_PHONE}'},
            {type: 'label', tag: '{AGENT_ADDRESS}'},
            {type: 'label', tag: '{AGENT_WEBSITE}'},
            {type: 'label', tag: '{AGENT_DISCLAIMER}'},
            {type: 'label', tag: '{CURRENT_DATE}'},
            {type: 'label', tag: '{CURRENT_MONTH}'},
            {type: 'label', tag: '{CURRENT_YEAR}'},
            {type: 'button', tag: '{PERFORM_CHECKOUT}', text: 'Checkout'},
            {type: 'button', tag: '{PERFORM_OPTIN}', text: 'Subscribe'},
        ];

        const formFields = @json($formFields ?? []);
        
        // window.addEventListener('load', function() {
        //     const iframe = document.getElementById('builder_iframe');
        //     if (iframe) {
        //         iframe.style.height = window.innerHeight + 'px';
        //     }
        //     let retries = 0;
        //     const maxRetries = 20; // ~6 seconds total
        //     const enforceIframeStyle = setInterval(() => {
        //         if (iframe) {
        //             iframe.style.setProperty('height', '100vh', 'important');
        //             iframe.style.setProperty('width', '100%', 'important');
        //             iframe.style.setProperty('border', 'none', 'important');
        //             iframe.style.setProperty('display', 'block', 'important');
        //             iframe.setAttribute('scrolling', 'yes');

        //             retries++;

        //             // If iframe has desired height, or retries exceed max, stop
        //             if (iframe.offsetHeight >= window.innerHeight || retries >= maxRetries) {
        //                 clearInterval(enforceIframeStyle);
        //             }
        //         }
        //     }, 300);

        // });

        document.addEventListener('DOMContentLoaded', () => {
            window.editor = new Editor({
                title: '{{ $title }}',
                buildMode: {{ $isLegacy ? 'false' : 'true' }},
                legacyMode: {{ $isLegacy ? 'true' : 'false' }},
                formMode: {{ $isForm ? 'true' : 'false' }},
                formFields: formFields,
                root: '/builder/dist/',
                url: '{{ $page_path }}',
                urlBack: window.location.origin,
                uploadAssetUrl: saveAssets,
                uploadAssetMethod: 'POST',
                uploadTemplateUrl: '/page_builder_api/upload',
                saveUrl: saveUrl,
                saveMethod: 'POST',
                templates: templates,
                tags: tags,
                changeTemplateCallback: function (url) {
                    window.location = url;
                },
                export: {
                    url: saveUrl
                },
                backgrounds: [
                    '/builder/assets/image/backgrounds/images1.jpg',
                    '/builder/assets/image/backgrounds/images2.jpg',
                    '/builder/assets/image/backgrounds/images3.jpg',
                    '/builder/assets/image/backgrounds/images4.png',
                    '/builder/assets/image/backgrounds/images5.jpg',
                    '/builder/assets/image/backgrounds/images6.jpg',
                    '/builder/assets/image/backgrounds/images9.jpg',
                    '/builder/assets/image/backgrounds/images11.jpg',
                    '/builder/assets/image/backgrounds/images12.jpg',
                    '/builder/assets/image/backgrounds/images13.jpg',
                    '/builder/assets/image/backgrounds/images14.jpg',
                    '/builder/assets/image/backgrounds/images15.jpg',
                    '/builder/assets/image/backgrounds/images16.jpg',
                    '/builder/assets/image/backgrounds/images17.png',
                ]
            });

            window.editor.init();

            window.editor.save = function (callback = null) {
                const iframe = document.getElementById('builder_iframe');
                const iframeDoc = iframe?.contentDocument || iframe?.contentWindow?.document;

                if (!iframeDoc) {
                    Swal.fire('Error', 'Unable to access builder content.', 'error');
                    return;
                }

                const html = iframeDoc.documentElement.outerHTML;

                // Step 1: Temporarily move iframe content into a full-page div for capture
                const cloneWrapper = document.createElement('div');
                const cloneFrame = document.createElement('iframe');
                cloneWrapper.style.position = 'fixed';
                cloneWrapper.style.left = '-9999px';
                cloneWrapper.style.top = '0';
                cloneWrapper.style.width = '1200px';
                cloneWrapper.style.height = '800px';

                document.body.appendChild(cloneWrapper);
                cloneWrapper.appendChild(cloneFrame);

                const blobHtml = new Blob([html], { type: 'text/html' });
                const url = URL.createObjectURL(blobHtml);
                cloneFrame.src = url;

                cloneFrame.onload = () => {
                const doc = cloneFrame.contentDocument || cloneFrame.contentWindow.document;

                // ✅ Manually inject CSS links into the iframe head
                const cssLinks = [
                    '/assets/builder/assets/css/style.css',
                    '/assets/builder/assets/css/blocks.css',
                    '/assets/builder/dist/builder.css'
                ];

                cssLinks.forEach(href => {
                    const link = doc.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = href;
                    link.type = 'text/css';
                    link.crossOrigin = 'anonymous'; // optional: if resources are CORS-safe
                    doc.head.appendChild(link);
                });

                // Allow time for CSS to apply
                setTimeout(() => {
                    html2canvas(doc.body, {
                        backgroundColor: '#ffffff',
                        scale: 0.4,
                        useCORS: true
                    }).then(canvas => {
                        document.body.removeChild(cloneWrapper);
                        URL.revokeObjectURL(url); // cleanup

                        canvas.toBlob(blob => {
                            const formData = new FormData();
                            formData.append('content', html);
                            formData.append('thumbnail', blob, 'thumb.png');
                            formData.append('_token', window.CSRF_TOKEN);

                            fetch(saveUrl, {
                                method: 'POST',
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(() => {
                                    $('.btn-save').removeClass('btn-saving').html('Draft Saved');
                                    setTimeout(() => { $('.btn-save').html('Save as Draft'); }, 2000);
                                    Swal.fire('Success', 'Saved successfully!', 'success');
                                    // ✅ Execute callback if provided
                                    if (typeof callback === 'function') {
                                        callback();
                                    }
                                })
                                .catch(err => {
                                    $('.btn-save').removeClass('btn-saving').html('Error');
                                    setTimeout(() => { $('.btn-save').html('Save as Draft'); }, 2000);
                                    console.error('Save failed:', err);
                                    Swal.fire('Error', 'Save failed. Check console for details.', 'error');
                                });
                            }, 'image/png');
                        });
                    }, 500); // wait half a second for styles to render
                };

            };

            setTimeout(() => {
                const iframe = document.getElementById('builder_iframe');
                if (iframe) {
                    iframe.style.height = window.innerHeight + 'px';
                }
                let retries = 0;
                const maxRetries = 20; // ~6 seconds total
                const enforceIframeStyle = setInterval(() => {
                    if (iframe) {
                        // iframe.style.setProperty('height', '100vh', 'important');
                        iframe.style.setProperty('width', '100%', 'important');
                        iframe.style.setProperty('border', 'none', 'important');
                        iframe.style.setProperty('display', 'block', 'important');
                        iframe.setAttribute('scrolling', 'yes');

                        retries++;

                        // If iframe has desired height, or retries exceed max, stop
                        if (iframe.offsetHeight >= window.innerHeight || retries >= maxRetries) {
                            clearInterval(enforceIframeStyle);
                        }
                    }
                }, 300);
                $(document).off("click", "a.save-design, .bp-save");
                $(document).off("click", ".menu-bar-action.btn-export");
                $(document).off("click", ".menu-bar-action.btn-close");

                // Custom Save
                $(document).on("click", ".btn-save", function (e) {
                    e.preventDefault();
                    $(this).addClass('btn-saving').html('Saving ...');
                    window.editor.save();
                });

                // Custom Save & Close
                $(document).on("click", ".btn-export", function (e) {
                    e.preventDefault();
                    $(this).addClass('btn-saving').html('Downloading ...');
                    window.editor.save(() => {
                        setTimeout(() => { window.location.href = "{{ route('page-builder.saves') }}"; }, 2000);
                    });
                });

                // Custom Close with confirmation
                $(document).on("click", ".btn-close", function (e) {
                    e.preventDefault();
                    window.location.href = "{{ route('page-builder.index') }}";
                });

            }, 500);
        });
    </script>
</head>
<body class="overflow-hidden"></body>
</html>
