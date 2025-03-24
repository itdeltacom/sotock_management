/**
 * CKEditor 5 Custom Configuration
 * This file defines the custom configuration for CKEditor 5 with advanced features
 * for blog content editing.
 */

function initCKEditor(elementId, uploadUrl) {
    return ClassicEditor
        .create(document.querySelector(elementId), {
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'outdent',
                    'indent',
                    '|',
                    'blockQuote',
                    'insertTable',
                    'mediaEmbed',
                    'undo',
                    'redo',
                    '|',
                    'alignment',
                    'fontColor',
                    'fontBackgroundColor',
                    'highlight',
                    '|',
                    'horizontalLine',
                    'removeFormat',
                    'imageUpload'
                ]
            },
            language: 'en',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side',
                    'linkImage'
                ],
                styles: [
                    'full',
                    'side',
                    'alignLeft',
                    'alignCenter',
                    'alignRight'
                ],
                upload: {
                    types: ['jpeg', 'png', 'gif', 'jpg', 'webp'],
                }
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells',
                    'tableCellProperties',
                    'tableProperties'
                ]
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
                ]
            },
            placeholder: 'Write your blog content here...',
            mediaEmbed: {
                previewsInData: true,
                providers: [
                    {
                        name: 'youtube',
                        url: /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?([^&]+)/,
                        html: match => {
                            const id = match[1];
                            return (
                                '<div class="embed-responsive embed-responsive-16by9">' +
                                `<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/${id}" ` +
                                'frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' +
                                '</div>'
                            );
                        }
                    },
                    {
                        name: 'vimeo',
                        url: /^(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com)\/(\d+)/,
                        html: match => {
                            const id = match[1];
                            return (
                                '<div class="embed-responsive embed-responsive-16by9">' +
                                `<iframe class="embed-responsive-item" src="https://player.vimeo.com/video/${id}" ` +
                                'frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>' +
                                '</div>'
                            );
                        }
                    }
                ]
            },
            // Enable the SimpleUpload adapter
            simpleUpload: {
                uploadUrl: uploadUrl,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            },
            // Customize the content styles
            typing: {
                transformations: {
                    include: [
                        // Common quotes transformation.
                        'quotes',
                        // Common typography transformations.
                        'typography',
                        // Additional transformations.
                        { from: '(c)', to: '©' },
                        { from: '(tm)', to: '™' },
                        { from: '(r)', to: '®' },
                        // Smart dash transformations.
                        { from: /(^|\s)--($|\s)/, to: '$1—$2' },
                    ]
                }
            }
        })
        .then(editor => {
            // Save a reference to the editor
            return editor;
        })
        .catch(error => {
            console.error('CKEditor initialization error:', error);
        });
}