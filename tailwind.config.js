/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                // Brand - warm red accent
                brand: {
                    50: '#fff5f5',
                    100: '#ffe0e0',
                    200: '#ffc2c2',
                    300: '#ff8a8a',
                    400: '#ff6b6b',
                    500: '#FF6363',
                    600: '#e85555',
                    700: '#cc4040',
                    800: '#b32e2e',
                    900: '#991f1f',
                },
                // Navy backgrounds
                navy: {
                    950: '#1A1A2E',
                    900: '#1E1E34',
                    850: '#22223A',
                    800: '#2A2A3E',
                    700: '#33334A',
                },
                // Surface gray
                surface: {
                    DEFAULT: '#2A2A3E',
                    light: '#33334A',
                    lighter: '#3A3A52',
                },
            },
            spacing: {
                '1': '4px',
                '2': '8px',
                '3': '12px',
                '4': '16px',
                '5': '20px',
                '6': '24px',
            },
            borderRadius: {
                'sm': '6px',
                'md': '10px',
                'lg': '14px',
            },
            boxShadow: {
                'card': '0 4px 16px rgba(0, 0, 0, 0.3)',
                'palette': '0 20px 60px rgba(0, 0, 0, 0.6)',
            },
            fontSize: {
                'title': ['24px', { lineHeight: '1.2', fontWeight: '700' }],
                'heading': ['18px', { lineHeight: '1.3', fontWeight: '600' }],
                'body': ['14px', { lineHeight: '1.5', fontWeight: '400' }],
                'detail': ['12px', { lineHeight: '1.4', fontWeight: '400' }],
            },
        },
    },
    plugins: [],
};
