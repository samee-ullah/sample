/**
 * @credits https://stackoverflow.com/a/30810322/5570920
 */

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    let success    = false;

    textArea.value = text;

    // Avoid scrolling to bottom
    textArea.style.top      = '0';
    textArea.style.left     = '0';
    textArea.style.position = 'fixed';

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        if (document.execCommand('copy')) {
            success = true;
        }
    } catch (err) {
    }

    document.body.removeChild(textArea);

    return success;
}

export default function copyTextToClipboard(text) {
    return new Promise((resolve, reject) => {
        if (!navigator.clipboard) {
            if (fallbackCopyTextToClipboard(text)) {
                resolve();
            } else {
                reject();
            }

            return;
        }

        navigator.clipboard.writeText(text).then(function() {
            resolve();
        }, function() {
            reject();
        });
    });
}