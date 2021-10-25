<?php

$EM_CONF['solradmin'] = [
    'title' => 'Solr Administration',
    'description' => 'With this module you can directly access your solr module, search records with an interface, delete some specifics records and do other operations (with multi core support)',
    'category' => 'module',
    'version' => '2.0.0',
    'state' => 'stable',
    'author' => 'CERDAN Yohann [Site-nGo]',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '10.4.0-11.5.99',
                ],
            'conflicts' =>
                [],
            'suggests' =>
                [],
        ],
];
