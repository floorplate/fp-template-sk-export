<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    |
    | List of styles.
    |
    */

    'styles' => [

        'highlight' => [
            'type' => 'span',
            'name' => 'Accent Color Text',
            'ident' => 'H',
            'icon' => 'plump/edit-paint-palette', // You can add an SVG icon string if desired
            'class' => 'text-accent-text-color',
            // 'cp_css' => 'color: #D98E32',
            'cp_css' => 'color: var(--accent-text-color);',
            'cp_badge' => false,
        ],

        'secondary' => [
            'type' => 'span',
            'name' => 'Secondary Color Text',
            'ident' => 'H',
            'icon' => 'plump/edit-paint-palette', // You can add an SVG icon string if desired
            'class' => 'text-secondary-text-color',
            // 'cp_css' => 'color: #D98E32',
            'cp_css' => 'color: var(--secondary-text-color);',
            'cp_badge' => false,
        ],


        // 'title' => [
        //     'type' => 'heading_1',
        //     'name' => 'Title',
        //     'ident' => 'T',
        //     'icon' => null,
        //     'class' => 'title',
        //     'cp_css' => 'text-decoration: underline; text-underline-offset: 0.1em; text-decoration-color: #c5ccd4',
        //     'cp_badge' => false,
        // ],

        // 'lead' => [
        //     'type' => 'paragraph',
        //     'name' => 'Lead',
        //     'ident' => 'L',
        //     'icon' => null,
        //     'class' => 'lead',
        //     'cp_css' => 'font-size: 1.25em; margin-top: -0.5em',
        //     'cp_badge' => false,
        // ],

        // 'brand' => [
        //     'type' => 'span',
        //     'name' => 'Brand Text',
        //     'ident' => 'B',
        //     'icon' => null,
        //     'class' => 'brand-text',
        //     'cp_css' => 'color: #ff269e;',
        //     'cp_badge' => false,
        // ],

        // 'action' => [
        //     'type' => 'link',
        //     'name' => 'Action',
        //     'ident' => 'A',
        //     'icon' => null,
        //     'class' => 'action',
        //     'cp_css' => 'background: #737f8d; color: white; padding: 0.2em 0.5em; border-radius: 4px',
        //     'cp_badge' => false,
        // ],

        // 'square_list' => [
        //     'type' => 'unordered_list',
        //     'name' => 'Square List',
        //     'ident' => '■',
        //     'icon' => null,
        //     'class' => 'square-list',
        //     'cp_css' => 'list-style-type: square',
        //     'cp_badge' => false,
        // ],

        // 'roman_list' => [
        //     'type' => 'ordered_list',
        //     'name' => 'Roman List',
        //     'ident' => 'IV',
        //     'icon' => null,
        //     'class' => 'roman-list',
        //     'cp_css' => [
        //         '&' => 'list-style-type: upper-roman',
        //         '& ol' => 'list-style-type: lower-roman',
        //     ],
        //     'cp_badge' => false,
        // ],

        // 'two_columns' => [
        //     'type' => 'div',
        //     'name' => 'Two Columns',
        //     'ident' => '❙ ❙',
        //     'icon' => null,
        //     'class' => 'two-columns',
        //     'cp_css' => 'column-count: 2; column-gap: 16px',
        //     'cp_badge' => true,
        // ],

        // 'three_columns' => [
        //     'type' => 'div',
        //     'name' => 'Three Columns',
        //     'ident' => '❙❙❙',
        //     'icon' => null,
        //     'class' => 'three-columns',
        //     'cp_css' => 'column-count: 3; column-gap: 16px',
        //     'cp_badge' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Pins (pro only)
    |--------------------------------------------------------------------------
    |
    | List of pins.
    |
    */

    'pins' => [

        // 'icon' => [
        //     'display' => 'Icon',
        //     'icon' => 'fire-flame-burn-hot',
        //     'instructions' => 'An icon graphic.',
        //     'fields' => [
        //         'src' => [
        //             'display' => 'Icon',
        //             'type' => 'assets',
        //             'max_files' => 1,
        //             'mode' => 'list',
        //         ],
        //     ],
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Attributes (pro only)
    |--------------------------------------------------------------------------
    |
    | The attributes that can be edited through the attributes panel.
    |
    */

    'attributes' => [

        // 'heading_1' => [
        //     'id' => [
        //         'type' => 'text',
        //         'display' => 'ID',
        //         'default' => null,
        //         'rendered' => true,
        //     ],
        //     'hero' => [
        //         'type' => 'toggle',
        //         'display' => 'Hero',
        //         'default' => null,
        //         'rendered' => 'class',
        //         'values' => [
        //             'true' => 'hero',
        //         ],
        //     ],
        // ],

        'heading' => [
            // 'large' => [
            //     'type' => 'toggle',
            //     'display' => 'Large',
            //     'default' => null,
            //     'rendered' => 'class',
            //     'values' => [
            //         'true' => 'large',
            //     ],
            // ],
            'font-weight' => [
                'type' => 'select',
                'display' => 'Font Weight',
                'default' => null,
                'rendered' => 'style',
                'clearable' => true,
                'options' => [
                    '100' => 'Thin',
                    '200' => 'Extra Light',
                    '300' => 'Light',
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold',
                    '900' => 'Black',
                ],
                'cp_css' => [
                    '100' => [
                        '&' => 'font-weight: 100 !important;',
                    ],
                    '200' => [
                        '&' => 'font-weight: 200 !important;',
                    ],
                    '300' => [
                        '&' => 'font-weight: 300 !important;',
                    ],
                    '400' => [
                        '&' => 'font-weight: 400 !important;',
                    ],
                    '500' => [
                        '&' => 'font-weight: 500 !important;',
                    ],
                    '600' => [
                        '&' => 'font-weight: 600 !important;',
                    ],
                    '700' => [
                        '&' => 'font-weight: 700 !important;',
                    ],
                    '800' => [
                        '&' => 'font-weight: 800 !important;',
                    ],
                    '900' => [
                        '&' => 'font-weight: 900 !important;',
                    ],
                ],
            ],
        ],

        'paragraph' => [
            // 'large' => [
            //     'type' => 'toggle',
            //     'display' => 'Large',
            //     'default' => null,
            //     'rendered' => 'class',
            //     'values' => [
            //         'true' => 'large',
            //     ],
            // ],
            'font-weight' => [
                'type' => 'select',
                'display' => 'Font Weight',
                'default' => null,
                'rendered' => 'style',
                'clearable' => true,
                'options' => [
                    '100' => 'Thin',
                    '200' => 'Extra Light',
                    '300' => 'Light',
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold',
                    '900' => 'Black',
                ],
                'cp_css' => [
                    '100' => [
                        '&' => 'font-weight: 100 !important;',
                    ],
                    '200' => [
                        '&' => 'font-weight: 200 !important;',
                    ],
                    '300' => [
                        '&' => 'font-weight: 300 !important;',
                    ],
                    '400' => [
                        '&' => 'font-weight: 400 !important;',
                    ],
                    '500' => [
                        '&' => 'font-weight: 500 !important;',
                    ],
                    '600' => [
                        '&' => 'font-weight: 600 !important;',
                    ],
                    '700' => [
                        '&' => 'font-weight: 700 !important;',
                    ],
                    '800' => [
                        '&' => 'font-weight: 800 !important;',
                    ],
                    '900' => [
                        '&' => 'font-weight: 900 !important;',
                    ],
                ],
            ],
        ],

        // 'ordered_list' => [
        //     'start' => [
        //         'type' => 'text',
        //         'display' => 'Start',
        //         'default' => null,
        //         'rendered' => true,
        //     ],
        //     'reversed' => [
        //         'type' => 'toggle',
        //         'display' => 'Reversed',
        //         'default' => null,
        //         'rendered' => true,
        //     ],
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Default styles that will be applied to elements with no style set. It's
    | also possible configure multiple sets of defaults to use with different
    | Bard fields, refer to the docs for more info.
    |
    */

    'defaults' => [

        // 'heading_1' => [
        //     'class' => 'heading-1',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],
        // 'heading_2' => [
        //     'class' => 'heading-2',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],
        // 'paragraph' => [
        //     'class' => 'paragraph',
        //     'cp_css' => null,
        //     'cp_badge' => false,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    |
    | By default the class names are saved to your content. If you would prefer
    | to save the style keys instead you can change this option to "key".
    |
    */

    'store' => 'class',

];
