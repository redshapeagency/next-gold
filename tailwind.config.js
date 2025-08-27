import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gold: {
                    50: '#fffdf7',
                    100: '#fffaeb',
                    200: '#fef3c7',
                    300: '#fde68a',
                    400: '#fcd34d',
                    500: '#fbbf24',
                    600: '#f59e0b',
                    700: '#d97706',
                    800: '#b45309',
                    900: '#92400e',
                },
            },
        },
    },

    plugins: [forms],
};
