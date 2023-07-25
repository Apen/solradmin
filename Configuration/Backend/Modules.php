<?php

return [
    'system_solradmin' => [
        'parent' => 'system',
        'extensionName' => 'solradmin',
        'labels' => 'LLL:EXT:solradmin/Resources/Private/Language/locallang.xlf:module.title',
        'iconIdentifier' => 'solradmin-main',
        'controllerActions' => [
            \Sng\Solradmin\Controller\AdminController::class => 'list,detail,delete',
        ],
    ],
];


