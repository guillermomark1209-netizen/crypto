'use strict';

const copyButton = document.getElementById('copy-result');

if (copyButton) {
    copyButton.addEventListener('click', async () => {
        const output = document.getElementById('cipher-result');
        const status = document.getElementById('copy-status');
        const text = output ? output.textContent : '';

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                const temporaryInput = document.createElement('textarea');
                temporaryInput.value = text;
                temporaryInput.setAttribute('readonly', '');
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();
                const copied = document.execCommand('copy');
                temporaryInput.remove();
                if (!copied) {
                    throw new Error('Clipboard copy was denied.');
                }
            }
            status.textContent = 'Copied to clipboard.';
        } catch (error) {
            status.textContent = 'Copy failed. Select the result and copy it manually.';
        }
    });
}
