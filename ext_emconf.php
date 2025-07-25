<?php

$EM_CONF['solradmin'] = [
    'title' => 'Solr Administration',
    'description' => 'With this module you can directly access your solr module, search records with an interface, delete some specifics records and do other operations (with multi core support)',
    'category' => 'module',
    'version' => '2.0.2',
    'state' => 'stable',
    'author' => 'CERDAN Yohann [Site-nGo]',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.99-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
