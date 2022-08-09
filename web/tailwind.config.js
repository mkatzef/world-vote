/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  safelist: [
    "bg-orange-300",
    "bg-orange-400",
    "bg-orange-500",
    "border-orange-300",
    "border-orange-400",
    "border-orange-500",
    "text-orange-300",
    "text-orange-400",
    "text-orange-500",
    "mx-1",
  ],
}
