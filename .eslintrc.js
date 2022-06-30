module.exports = {
    env: {
        browser: true,
        es2020: true,
    },
    extends: ['google', 'prettier'],
    parserOptions: {
        sourceType: 'module',
    },
    rules: {
        camelcase: ['warn', { ignoreGlobals: true }],
        'space-in-parens': ['error', 'never'],
        'no-unused-vars': ['error', { varsIgnorePattern: '^[A-Z][a-z]+' }],
    },
};
