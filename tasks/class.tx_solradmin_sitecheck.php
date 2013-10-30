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


class tx_solradmin_sitecheck extends tx_scheduler_Task {
	protected $urlDelete = array();

	/**
	 * Execute the task
	 *
	 * @return bool
	 */
	public function execute() {
		require_once(PATH_site . 'typo3conf/ext/solradmin/classes/class.tx_solradmin_connection.php');
		$site = $this->site;

		$solrConnections = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getAllConnections();
		$this->solrAdminConnection = new tx_solradmin_connection($solrConnections[0]);

		$query = 'site:' . $this->solrAdminConnection->escapeUrlvalue($site) . '*';
		$offset = 0;
		$limit = $this->limit;
		$params = array('qt' => 'standard');
		$response = $this->solrAdminConnection->search($query, $offset, $limit, $params);

		if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
			echo '---------------------------------------------' . LF;
			echo 'Checking query "' . $query . '" (' . intval($response->response->numFound) . ' results) --> limit ' . $limit . LF;
			echo '---------------------------------------------' . LF;
		}

		foreach ($response->response->docs as $doc) {
			if (substr($doc->url, 0, 4) == 'http') {
				$url = $doc->url;
			} else {
				$url = $site . $doc->url;
			}
			//echo $url;
			if (($checkUrl = $this->checkUrl($url)) === TRUE) {
				//echo ' --> OK' . LF;
			} else {
				$this->urlDelete[] = array('id' => $doc->id, 'created' => $doc->created, 'indexed' => $doc->indexed, 'url' => $url);
				//echo ' --> !!!!! KO !!!!!' . LF;
			}
		}

		if (empty($this->delete)) {
			if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
				echo 'Just checking url and not delete them...' . LF;
			}
		} else {
			if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
				echo 'Check and delete url...' . LF;
			}
			foreach ($this->urlDelete as $deleteRecord) {
				$delete = tx_solradmin_connection::escape($deleteRecord['id']);
				$this->solrAdminConnection->getSolrConnection()->commit();
				$this->solrAdminConnection->getSolrConnection()->deleteByQuery('id:' . $delete);
				$this->solrAdminConnection->getSolrConnection()->commit();
			}
		}

		if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
			echo '---------------------------------------------' . LF;
			echo 'Bad urls (' . count($this->urlDelete) . ' results)' . LF;
			echo '---------------------------------------------' . LF;
			print_r($this->urlDelete);
		}

		return TRUE;
	}

	/**
	 * Check a URL
	 *
	 * @param string $url
	 * @return bool
	 */
	public function checkUrl($url) {
		$file = @fopen($url, 'r');
		if ($file) {
			fclose($file);
			return TRUE;
		} else {
			return $http_response_header;
		}
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
		return 'SITE:' . $this->site . ' - LIMIT:' . $this->limit;
	}

}

?>
