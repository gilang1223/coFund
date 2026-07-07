import { ref, watch } from 'vue';

/**
 * Theme composable for light/dark mode.
 * Persists preference in localStorage and applies to document.documentElement.
 */
const isDarkMode = ref(true); // default dark

// Load saved preference
const saved = localStorage.getItem('cofund_theme');
if (saved === 'light') {
    isDarkMode.value = false;
} else if (saved === 'dark') {
    isDarkMode.value = true;
}

// Apply initial theme — only toggle 'light-mode' class, no separate 'dark-mode' class needed.
// When light-mode is absent, the default dark theme (from Tailwind/PrimeVue) applies.
function applyTheme(dark) {
    if (dark) {
        document.documentElement.classList.remove('light-mode');
    } else {
        document.documentElement.classList.add('light-mode');
    }
}
applyTheme(isDarkMode.value);

export function useTheme() {
    function toggleTheme() {
        isDarkMode.value = !isDarkMode.value;
        localStorage.setItem('cofund_theme', isDarkMode.value ? 'dark' : 'light');
        applyTheme(isDarkMode.value);
    }

    function setTheme(dark) {
        isDarkMode.value = dark;
        localStorage.setItem('cofund_theme', dark ? 'dark' : 'light');
        applyTheme(dark);
    }

    return {
        isDarkMode,
        toggleTheme,
        setTheme,
    };
}
