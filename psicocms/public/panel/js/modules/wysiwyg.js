const BASE_BUTTONS = ['paragraph', '|', 'bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link'];
const END_BUTTONS = ['table', '|', 'undo', 'redo'];

function buildConfig(textarea) {
    const uploadUrl = textarea.dataset.uploadUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const config = {
        language: 'es',
        height: Number(textarea.dataset.height || 360),
        theme: document.documentElement.dataset.mode === 'dark' ? 'dark' : 'default',
        toolbarAdaptive: false,
        buttons: uploadUrl ? [...BASE_BUTTONS, 'image', ...END_BUTTONS] : [...BASE_BUTTONS, ...END_BUTTONS],
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
            headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
            filesVariableName: () => 'image',
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
