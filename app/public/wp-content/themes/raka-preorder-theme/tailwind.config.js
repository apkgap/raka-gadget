/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './*.php',
    './inc/**/*.php',
    './template-parts/**/*.php',
    './woocommerce/**/*.php',
    './src/**/*.{js,jsx,ts,tsx}',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        obsidian: '#080808',
        cement: '#DADADA',
        cementDark: '#121212',
      },
      fontFamily: {
        hn: ['"Helvetica Neue ME"', '"Helvetica Neue"', 'Prompt', 'Kanit', 'Inter', 'Helvetica', 'Arial', 'sans-serif'],
        kanit: ['Kanit', 'Prompt', 'sans-serif'],
        prompt: ['Prompt', 'Kanit', 'sans-serif'],
        sans: ['"Helvetica Neue ME"', '"Helvetica Neue"', 'Prompt', 'Kanit', 'Inter', 'Helvetica', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
