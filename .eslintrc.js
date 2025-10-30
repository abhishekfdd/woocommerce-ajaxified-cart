module.exports = {
  env: {
    browser: true,
    es6: true,
    jquery: true
  },
  extends: ["eslint:recommended"],
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module'
  },
  rules: {
    'no-unused-vars': ['warn', { args: 'none' }],
    'no-console': 'off'
  }
};

