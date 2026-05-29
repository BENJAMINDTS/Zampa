import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                display: ['Playfair Display',  ...defaultTheme.fontFamily.serif],
            },

            colors: {
                // ── Zampa brand — cognac amber ─────────────────────────────
                zampa: {
                    50:  '#FDF6EC',
                    100: '#FAE8CA',
                    200: '#F5CE93',
                    300: '#EFAD55',
                    400: '#E7922A',
                    500: '#D97B1A',   // main brand color
                    600: '#B86315',
                    700: '#944E12',
                    800: '#763E12',
                    900: '#5E3311',
                    950: '#341A08',
                },
                // ── Surface — warm UI grays (sidebar, cards, panels) ───────
                surface: {
                    50:  '#F9F8F7',
                    100: '#F1EFEC',
                    200: '#E4E0DA',
                    300: '#CBC4BB',
                    400: '#AFA69A',
                    500: '#8F847A',
                    600: '#746B62',
                    700: '#5F5650',
                    800: '#4A4440',
                    900: '#2C2825',
                    950: '#1A1714',
                },
            },

            boxShadow: {
                card:      '0 1px 3px 0 rgb(0 0 0 / 0.08), 0 1px 2px -1px rgb(0 0 0 / 0.08)',
                'card-md': '0 4px 6px -1px rgb(0 0 0 / 0.07), 0 2px 4px -2px rgb(0 0 0 / 0.07)',
                'card-lg': '0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.07)',
            },
        },
    },

    plugins: [forms],
};
