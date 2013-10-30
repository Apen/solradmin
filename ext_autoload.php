<?php

$tasks = array('sitecheck', 'post');
$loadArray = array();
$extensionPath = t3lib_extMgm::extPath('solradmin');

foreach ($tasks as $task) {
	$loadArray['tx_solradmin_' . $task] = $extensionPath . 'tasks/class.tx_solradmin_' . $task . '.php';
	$loadArray['tx_solradmin_' . $task . '_fields'] = $extensionPath . 'tasks/class.tx_solradmin_' . $task . '_fields.php';
}

return $loadArray;

?>