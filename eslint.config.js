const js = require('@eslint/js');

module.exports = [
  { ignores: ['assets/js/dist/**', 'assets/js/*.min.js'] },
  js.configs.recommended,
  {
    files: ['assets/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        jQuery: 'readonly',
        wc_add_to_cart_params: 'readonly',
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        abwc_ajax_data: 'readonly'
      }
    },
    rules: {
      'no-unused-vars': ['warn', { args: 'none' }],
      'no-console': 'off',
      'no-undef': 'off'
    }
  }
];
