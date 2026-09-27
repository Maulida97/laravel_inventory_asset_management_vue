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

    const toggleTheme = () => {
        applyTheme(theme.value === 'dark' ? 'light' : 'dark');
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