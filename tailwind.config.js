module.exports = {
  content: [
    './app/**/*.{js,jsx}',
    './components/**/*.{js,jsx}'
  ],
  theme: {
    extend: {
      colors: {
        primary: '#E50914',
        secondary: '#221F1F',
        accent: '#F5F5F1',
        dark: '#000000'
      },
      fontFamily: {
        sans: ['Helvetica Neue', 'Arial', 'sans-serif']
      }
    }
  },
  plugins: []
};
