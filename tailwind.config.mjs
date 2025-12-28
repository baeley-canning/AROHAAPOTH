/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        background: "rgb(var(--background) / <alpha-value>)",
        foreground: "rgb(var(--foreground) / <alpha-value>)",
        ink: {
          900: "rgb(var(--ink-900) / <alpha-value>)",
          700: "rgb(var(--ink-700) / <alpha-value>)",
          500: "rgb(var(--ink-500) / <alpha-value>)",
        },
        sage: {
          700: "rgb(var(--sage-700) / <alpha-value>)",
          600: "rgb(var(--sage-600) / <alpha-value>)",
          500: "rgb(var(--sage-500) / <alpha-value>)",
          300: "rgb(var(--sage-300) / <alpha-value>)",
        },
        clay: {
          600: "rgb(var(--clay-600) / <alpha-value>)",
          500: "rgb(var(--clay-500) / <alpha-value>)",
          300: "rgb(var(--clay-300) / <alpha-value>)",
        },
        linen: {
          100: "rgb(var(--linen-100) / <alpha-value>)",
          50: "rgb(var(--linen-50) / <alpha-value>)",
        },
        mist: {
          200: "rgb(var(--mist-200) / <alpha-value>)",
        },
        blush: {
          200: "rgb(var(--blush-200) / <alpha-value>)",
        },
      },
      gridTemplateColumns:{
        'auto': 'repeat(auto-fit, minmax(200px, 1fr))'
      },
    },
  },
  plugins: [],
};
