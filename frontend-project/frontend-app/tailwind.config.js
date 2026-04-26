/** @type {import('tailwindcss').Config} */
export default {
    content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                background: '#0e0e0e',
                surface: '#131313',
                'surface-low': '#1c1b1b',
                'surface-high': '#2a2a2a',
                border: '#353534',
                primary: '#6dffba',
                'primary-container': '#00e599',
                error: '#ffb4ab',
                'on-surface': '#e5e2e1',
                'on-surface-variant': '#bacbbe',
                'emerald-400': '#34d399',
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular'],
            },
            spacing: {
                'sidebar-width': '260px',
                'header-height': '56px',
            },
            borderRadius: {
                sm: '2px',
            },
        },
    },
    plugins: [import('@tailwindcss/forms')],
};
