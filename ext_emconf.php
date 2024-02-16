<?php
$EM_CONF['headless_mask'] = [
    'title' => 'Headless Mask',
    'description' => 'Support headless for the mask elements',
    'state' => 'stable',
    'author' => 'T3: Nilesh Malankiya',
    'author_email' => 'info@nitsan.in',
    'category' => 'fe',
    'internal' => '',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.5.99',
            'frontend' => '12.0.0-12.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'headless' => '2.0.0-4.9.9',
        ],
    ],
];
