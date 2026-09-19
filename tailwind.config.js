import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta principal: NARANJA (identidad de restaurante)
                brand: {
                    50:  '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 300: '#fdba74',
                    400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c',
                    800: '#9a3412', 900: '#7c2d12', 950: '#431407',
                },
                // Acento: ROJO TOMATE (combina con naranja, da contraste y semántica de error)
                accent: {
                    50: '#fef2f2', 100: '#fee2e2', 400: '#f87171', 500: '#ef4444', 600: '#dc2626', 700: '#b91c1c',
                },
            },
        },
    },
    safelist: [
        { pattern: /(bg|text|border)-(emerald|rose|amber|indigo|sky|slate|orange)-(50|100|500|600)/ },
    ],
    plugins: [forms],
};
