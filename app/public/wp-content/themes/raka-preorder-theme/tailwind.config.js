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
    '../../plugins/my-wc-passkeys/templates/**/*.php',
    '../../plugins/my-wc-passkeys/includes/**/*.php',
    '../../plugins/my-wc-passkeys/assets/js/**/*.js',
    '../../plugins/preorder-raka/*.php',
    '../../plugins/preorder-raka/inc/**/*.php',
    '../../plugins/preorder-raka/templates/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        obsidian: '#080808',
        cement: '#DADADA',
        cementDark: '#121212',
      },
      fontFamily: {
        hn: ['"Helvetica Neue ME"', '"Helvetica Neue"', 'Prompt', 'sans-serif'],
        prompt: ['Prompt', 'sans-serif'],
        sans: ['"Helvetica Neue ME"', '"Helvetica Neue"', 'Prompt', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
