<?php

declare(strict_types=1);

defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solradmin',
    'Configuration/TypoScript',
    'Solr Admin'
);
