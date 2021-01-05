<?php

defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Sng.Solradmin',
    'system',
    'solradmin',
    '',
    [
        'Admin' => 'list,detail,delete'
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:solradmin/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:solradmin/Resources/Private/Language/locallang.xlf:module.title',
    ]
);
