<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 CERDAN Yohann (cerdanyohann@yahoo.fr)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


class tx_solradmin_post extends tx_scheduler_Task {

	/**
	 * Execute the task
	 *
	 * @return bool
	 */
	public function execute() {
		require_once(PATH_site . 'typo3conf/ext/solradmin/classes/class.tx_solradmin_connection.php');
		$solrConnections = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getAllConnections();
		$this->solrAdminConnection = new tx_solradmin_connection($solrConnections[0]);

		if (!empty($this->post)) {
			$response = $this->solrAdminConnection->add($this->post);
			if ($response->getHttpStatus() != 200) {
				return FALSE;
			} else {
				return TRUE;
			}
		}

		if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
			/*echo '---------------------------------------------' . LF;
			echo 'Bad urls (' . count($this->urlDelete) . ' results)' . LF;
			echo '---------------------------------------------' . LF;
			print_r($this->urlDelete);*/
		}

		return TRUE;
	}


	/**
	 * This method is designed to return some additional information about the task,
	 * that may help to set it apart from other tasks from the same class
	 * This additional information is used - for example - in the Scheduler's BE module
	 * This method should be implemented in most task classes
	 *
	 * @return    string    Information to display
	 */

	public function getAdditionalInformation() {
		//return 'SITE:' . $this->site . ' - LIMIT:' . $this->limit;
	}

}

?>
