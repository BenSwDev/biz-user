let translations = {};
const rtlLanguages = ['he', 'ar', 'fa', 'ur'];

function safeExecute(description, fn) {
    try { fn(); } catch (error) {
        console.error(`Error during ${description}:`, error);
    }
}

fetch('translations.json')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        translations = data;
        console.log("Translations loaded successfully.");
        const languagePicker = document.getElementById('language-picker');
        const currentLang = languagePicker ? languagePicker.value : 'he';
        safeExecute("initial translation", () => {
            translatePage(currentLang);
        });

        const holder = document.querySelector('.holder');
        if (holder) holder.style.display = 'none';
        document.documentElement.style.visibility = 'visible';
        document.body.style.visibility = 'visible';
        console.log("Page is now visible.");
        observeNewNodes();
    })
    .catch(error => {
        console.error('Error loading translations:', error);
        const holder = document.querySelector('.holder');
        if (holder) holder.style.display = 'none';
        document.documentElement.style.visibility = 'visible';
        document.body.style.visibility = 'visible';
        console.log("Page is now visible despite errors.");
    });

function setDirection(lang) {
    try {
        if (rtlLanguages.includes(lang)) {
            document.documentElement.setAttribute('dir', 'rtl');
        } else {
            document.documentElement.setAttribute('dir', 'ltr');
        }
    } catch (error) {
        console.error("Error in setDirection:", error);
    }
}

function normalizeForMatch(str) {
    // Remove digits and punctuation for matching keys
    return str.replace(/[0-9\!\@\#\$\%\^\&\*\(\)\[\]\{\}\+\=\_\-\.\,\:\;\"\'\/\\\|<>\?\~`₪]/g, '').replace(/\s+/g,' ').trim();
}

function findTranslation(originalText, lang) {
    const trimmed = originalText.trim();
    if (!translations[lang]) return null;

    // Direct match
    if (translations[lang][trimmed]) {
        return translations[lang][trimmed];
    }

    // Fuzzy match by normalization
    const originalNorm = normalizeForMatch(trimmed);
    if (!originalNorm) return null;

    for (const key of Object.keys(translations[lang])) {
        const keyNorm = normalizeForMatch(key);
        if (keyNorm === originalNorm) {
            return translations[lang][key];
        }
    }

    return null;
}

/**
 * Extract numbers and replace them with placeholders.
 */
function extractNumbers(originalText) {
    let numberMatches = [];
    let placeholderIndex = 0;
    // Replace numbers with %NUM% placeholders
    let textWithPlaceholders = originalText.replace(/\b\d+(\.\d+)?\b/g, (match) => {
        numberMatches.push(match);
        return `%NUM_${placeholderIndex++}%`;
    });
    return {textWithPlaceholders, numberMatches};
}

/**
 * Reinsert numbers into the translated text in place of %NUM_x% placeholders.
 */
function reinsertNumbers(translatedText, numberMatches) {
    let finalText = translatedText;
    numberMatches.forEach((num, i) => {
        finalText = finalText.replace(`%NUM_${i}%`, num);
    });
    return finalText;
}

/**
 * Translate a string, handling numbers:
 * 1. Extract numbers into placeholders
 * 2. Attempt translation of the cleaned string
 * 3. If found, reinsert numbers
 */
function translateString(originalText, lang) {
    const {textWithPlaceholders, numberMatches} = extractNumbers(originalText);
    const baseTranslation = findTranslation(textWithPlaceholders, lang);
    if (baseTranslation && baseTranslation !== originalText) {
        return reinsertNumbers(baseTranslation, numberMatches);
    }
    return originalText;
}

function translateElement(element, lang) {
    safeExecute("translateElement text nodes", () => {
        const textNodes = Array.from(element.childNodes).filter(node => node.nodeType === Node.TEXT_NODE);
        for (let node of textNodes) {
            const originalText = node.textContent;
            let finalTranslation = translateString(originalText, lang);
            if (finalTranslation !== originalText) {
                node.textContent = finalTranslation;
            }
        }
    });

    safeExecute("translateAttributes", () => translateAttributes(element, lang));
    safeExecute("translateAdditionalAttributes", () => translateAdditionalAttributes(element, lang));
    safeExecute("translateSelectOptions", () => translateSelectOptions(element, lang));
    safeExecute("translateSVGText", () => {
        if (element instanceof SVGElement) {
            translateSVGText(element, lang);
        }
    });
    safeExecute("translateSpecificElements", () => translateSpecificElements(element, lang));
    safeExecute("translateTextareaPlaceholders", () => translateTextareaPlaceholders(element, lang));
    safeExecute("translateTooltips", () => translateTooltips(element, lang));
    // Inline event handlers not translated
}

function translateAttributes(element, lang) {
    const attributes = ['placeholder', 'title', 'aria-label', 'value'];
    for (let attr of attributes) {
        if (element.hasAttribute(attr)) {
            const original = element.getAttribute(attr).trim();
            let finalTranslation = translateString(original, lang);
            if (finalTranslation !== original) {
                if (attr === 'value' && ['INPUT','BUTTON','SELECT'].includes(element.tagName)) {
                    element.value = finalTranslation;
                } else {
                    element.setAttribute(attr, finalTranslation);
                }
            }
        }
    }
}

function translateAdditionalAttributes(element, lang) {
    if (element.hasAttribute('alt')) {
        const originalAlt = element.getAttribute('alt').trim();
        let finalTranslation = translateString(originalAlt, lang);
        if (finalTranslation !== originalAlt) {
            element.setAttribute('alt', finalTranslation);
        }
    }

    Array.from(element.attributes).forEach(attr => {
        if (attr.name.startsWith('data-') && !['data-phone', 'data-sms', 'data-mail'].includes(attr.name)) {
            const originalData = attr.value.trim();
            let finalTranslation = translateString(originalData, lang);
            if (finalTranslation !== originalData) {
                element.setAttribute(attr.name, finalTranslation);
            }
        }
    });
}

function translateSelectOptions(element, lang) {
    if (element.tagName.toLowerCase() === 'select') {
        const options = element.querySelectorAll('option');
        options.forEach(option => {
            const originalText = option.textContent.trim();
            let finalTranslation = translateString(originalText, lang);
            if (finalTranslation !== originalText) {
                option.textContent = finalTranslation;
            }
        });
    }
}

function translateSVGText(svgElement, lang) {
    const textElements = svgElement.querySelectorAll('text');
    textElements.forEach(textEl => {
        const originalText = textEl.textContent.trim();
        let finalTranslation = translateString(originalText, lang);
        if (finalTranslation !== originalText) {
            textEl.textContent = finalTranslation;
        }
    });
}

function translateSpecificElements(element, lang) {
    if (element.classList.contains('translate')) {
        const originalText = element.textContent.trim();
        let finalTranslation = translateString(originalText, lang);
        if (finalTranslation !== originalText) {
            element.textContent = finalTranslation;
        }
    }

    if (element.hasAttribute('data-translate')) {
        const originalText = element.getAttribute('data-translate').trim();
        let finalTranslation = translateString(originalText, lang);
        if (finalTranslation !== originalText) {
            element.textContent = finalTranslation;
        }
    }
}

function translateTextareaPlaceholders(element, lang) {
    if (element.tagName.toLowerCase() === 'textarea' && element.hasAttribute('placeholder')) {
        const originalPlaceholder = element.getAttribute('placeholder').trim();
        let finalTranslation = translateString(originalPlaceholder, lang);
        if (finalTranslation !== originalPlaceholder) {
            element.setAttribute('placeholder', finalTranslation);
        }
    }
}

function translateTooltips(element, lang) {
    const tooltipAttributes = ['data-tooltip', 'title'];
    tooltipAttributes.forEach(attr => {
        if (element.hasAttribute(attr)) {
            const originalTooltip = element.getAttribute(attr).trim();
            let finalTranslation = translateString(originalTooltip, lang);
            if (finalTranslation !== originalTooltip) {
                element.setAttribute(attr, finalTranslation);
            }
        }
    });
}

function translateAllElements(lang) {
    const allElements = document.querySelectorAll('*');
    allElements.forEach(element => translateElement(element, lang));
}

function translatePage(lang) {
    if (!translations[lang]) {
        console.warn(`No translations for language: ${lang}`);
    }
    setDirection(lang);
    translateAllElements(lang);
    console.log(`Page translation to ${lang} completed successfully.`);
}

function translateAllElementsForNode(node, lang) {
    translateElement(node, lang);
    const descendants = node.querySelectorAll('*');
    descendants.forEach(child => translateElement(child, lang));
    console.log("All elements inside the new node have been translated.");
}

function observeNewNodes() {
    const languagePicker = document.getElementById('language-picker');
    const currentLang = languagePicker ? languagePicker.value : 'he';

    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    translateAllElementsForNode(node, currentLang);
                }
            });
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

document.addEventListener("DOMContentLoaded", function() {
    safeExecute("language picker initialization", () => {
        const languagePicker = document.getElementById('language-picker');
        if (languagePicker && typeof $().select2 === 'function') {
            $(languagePicker).select2({ minimumResultsForSearch: -1 });
            languagePicker.addEventListener('change', function() {
                const selectedLang = this.value;
                translatePage(selectedLang);
            });
        }
    });
});
