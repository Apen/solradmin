<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	// module
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('tools_txsolradminM1', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools', 'txsolradminM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
	// module info
	require_once(PATH_site.'typo3conf/ext/solradmin/modfunc1/class.tx_solradmin_modfunc1.php');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_solradmin_modfunc1',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'modfunc1/class.tx_solradmin_modfunc1.php',
		'Solr Admin'
	);
}

$tasks = array('sitecheck', 'post');

foreach ($tasks as $task) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_' . $_EXTKEY . '_' . $task] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/mod1/locallang.xml:task.' . $task . '.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/mod1/locallang.xml:task.' . $task . '.description',
		'additionalFields' => 'tx_' . $_EXTKEY . '_' . $task . '_fields'
	);
}

?>
