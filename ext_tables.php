<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Solradmin',
    'system',
    'solradmin',
    '',
    [
        \Sng\Solradmin\Controller\AdminController::class => 'list,detail,delete'
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:solradmin/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:solradmin/Resources/Private/Language/locallang.xlf:module.title',
    ]
);
