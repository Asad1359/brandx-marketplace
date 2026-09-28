import { ref, onMounted, onUnmounted } from 'vue';

const STORAGE_KEY = 'brandx_theme';

const theme = ref('system');

let systemMedia = null;
let listener = null;

function apply() {
    const html = document.documentElement;
    html.classList.remove('dark', 'light');

    let isDark = false;

    if (theme.value === 'dark') {
        isDark = true;
    } else if (theme.value === 'light') {
        isDark = false;
    } else if (theme.value === 'system' && window.matchMedia) {
        isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    html.classList.toggle('dark', isDark);
    html.classList.toggle('light', !isDark);
    html.setAttribute('data-theme', theme.value);

    document.body.classList.toggle('dark-mode', isDark);
    document.body.classList.toggle('light-mode', !isDark);
}

export function useTheme() {
    function setTheme(value) {
        if (!['light', 'dark', 'system'].includes(value)) {
            return;
        }

        theme.value = value;
        localStorage.setItem(STORAGE_KEY, value);
        apply();
    }

    onMounted(() => {
        const saved = localStorage.getItem(STORAGE_KEY);

        if (saved) {
            theme.value = saved;
        }

        apply();

        if (window.matchMedia) {
            systemMedia = window.matchMedia('(prefers-color-scheme: dark)');

            listener = () => {
                if (theme.value === 'system') {
                    apply();
                }
            };

            if (systemMedia.addEventListener) {
                systemMedia.addEventListener('change', listener);
            } else {
                systemMedia.addListener(listener);
            }
        }
    });

    onUnmounted(() => {
        if (systemMedia && listener) {
            if (systemMedia.removeEventListener) {
                systemMedia.removeEventListener('change', listener);
            } else {
                systemMedia.removeListener(listener);
            }
        }
    });

    return { theme, setTheme, apply };
}

export function applyStoredTheme() {
    const saved = localStorage.getItem(STORAGE_KEY) || 'system';

    theme.value = saved;
    apply();
}

export { theme as currentTheme };