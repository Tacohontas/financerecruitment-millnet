module.exports = {
    extends: ['stylelint-config-standard-scss', 'stylelint-config-recess-order', 'stylelint-config-prettier'],

    plugins: ['stylelint-order', 'stylelint-declaration-block-no-ignored-properties'],

    rules: {
        'plugin/declaration-block-no-ignored-properties': true,
        'order/order': [
            'custom-properties',
            'dollar-variables',
            {
                type: 'at-rule',
                name: 'extend',
            },
            {
                type: 'at-rule',
                name: 'include',
                hasBlock: false,
            },
            'declarations',
            {
                type: 'at-rule',
                name: 'include',
                hasBlock: true,
            },
            {
                type: 'at-rule',
                name: 'media',
                hasBlock: true,
            },
            {
                type: 'rule',
                selector: '^&:(before|after)',
            },
            {
                type: 'rule',
                selector: '^&::(before|after)',
            },
            {
                type: 'rule',
                selector: '^&:(first-child|last-child|nth-child|last-of-type|first-of-type|nth-of-type)',
            },
            {
                type: 'rule',
                selector: '&:hover',
            },
            {
                type: 'rule',
                selector: '&:focus',
            },
            {
                type: 'rule',
                selector: '&:active',
            },
            {
                type: 'rule',
                selector: '&:disabled',
            },
            'rules',
        ],
    },
};
