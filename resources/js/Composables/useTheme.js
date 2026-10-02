import { ref, onMounted } from 'vue';

const theme = ref('dark');

export function useTheme() {
    const applyTheme = (newTheme) => {
        theme.value = newTheme;
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('data-theme', newTheme);
            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('assetflow-theme', newTheme);
        }
    };

    let transitionTimeout = null;

    const toggleTheme = () => {
        if (typeof document !== 'undefined') {
            document.documentElement.classList.add('theme-transitioning');
        }

        applyTheme(theme.value === 'dark' ? 'light' : 'dark');

        if (typeof window !== 'undefined') {
            if (transitionTimeout) clearTimeout(transitionTimeout);
            transitionTimeout = setTimeout(() => {
                document.documentElement.classList.remove('theme-transitioning');
            }, 400);
        }
    };

    const initTheme = () => {
        if (typeof window !== 'undefined') {
            const saved = localStorage.getItem('assetflow-theme') || 'dark';
            applyTheme(saved);
        }
    };

    return {
        theme,
        toggleTheme,
        applyTheme,
        initTheme,
    };
}