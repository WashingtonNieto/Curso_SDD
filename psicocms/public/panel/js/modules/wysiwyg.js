const PARAGRAPH_OPTIONS = { p: 'Párrafo', h2: 'Título 2', h3: 'Título 3', blockquote: 'Cita' };
const BASE_BUTTONS = ['paragraph', '|', 'bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link'];
const END_BUTTONS = ['table', '|', 'undo', 'redo'];
const DEFAULT_HEIGHT = 400;

function buildConfig(textarea) {
    const uploadUrl = textarea.dataset.uploadUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const Jodit = window.Jodit;

    const config = {
        language: 'es',
        height: Number(textarea.dataset.height || DEFAULT_HEIGHT),
        theme: document.documentElement.dataset.mode === 'dark' ? 'dark' : 'default',
        toolbarAdaptive: false,
        buttons: uploadUrl ? [...BASE_BUTTONS, 'image', ...END_BUTTONS] : [...BASE_BUTTONS, ...END_BUTTONS],
        controls: {
            paragraph: { list: typeof Jodit.atom === 'function' ? Jodit.atom(PARAGRAPH_OPTIONS) : PARAGRAPH_OPTIONS },
        },
        showCharsCounter: false,
        showWordsCounter: false,
        showXPathInStatusbar: false,
        askBeforePasteHTML: false,
        askBeforePasteFromWord: false,
        defaultActionOnPaste: 'insert_clear_html',
        placeholder: textarea.getAttribute('placeholder') || '',
        disablePlugins: ['about', 'powered-by-jodit', 'speech-recognize', 'ai-assistant'],
    };

    if (uploadUrl) {
        config.uploader = {
            url: uploadUrl,
            format: 'json',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } : { Accept: 'application/json' },
            filesVariableName: (index) => `images[${index}]`,
            insertImageAsBase64URI: false,
        };
    }

    return config;
}

export function initWysiwyg(root = document) {
    if (!window.Jodit) {
        return;
    }

    root.querySelectorAll('textarea[data-wysiwyg]').forEach((textarea) => {
        if (textarea.dataset.wysiwygReady) {
            return;
        }

        textarea.dataset.wysiwygReady = '1';
        window.Jodit.make(textarea, buildConfig(textarea));
    });
}
