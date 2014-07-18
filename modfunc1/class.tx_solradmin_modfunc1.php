<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 CERDAN Yohann <cerdanyohann@yahoo.fr>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


if (version_compare(TYPO3_version, '6.2.0', '<')) {
	require_once(PATH_t3lib . 'class.t3lib_extobjbase.php');
}
require_once(PATH_site . 'typo3conf/ext/solradmin/classes/class.tx_solradmin_connection.php');

/**
 * Module extension (addition to function menu) 'solradmin' for the 'solradmin' extension.
 *
 * @author        CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package       TYPO3
 * @subpackage    tx_solradmin
 */
class tx_solradmin_modfunc1 extends t3lib_extobjbase {
	/**
	 * Main method of the module
	 *
	 * @return    HTML
	 */

	function main() {
		global $LANG;
		$LANG->includeLLFile('EXT:solradmin/mod1/locallang.xml');
		$id = t3lib_div::_GP('id');
		$content = '';
		$content .= '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
				function deleteRecord(url)	{	//
					if (confirm(' . $LANG->JScharCode($LANG->getLL('areyousure')) . '))	{
						jumpToUrl(url);
					}
					return false;
				}
			</script>
		';
		if ($id > 0) {
			$solrConnection = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getConnectionByPageId($id);
			$solrAdminConnection = new tx_solradmin_connection($solrConnection);
			$solrAdminConnection->checkDelete();
			$site = $solrAdminConnection->escape(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
			$host = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			//$query = 'uid:' . intval($id) . ' AND (site:' . $site . ' OR site:' . $host . ')';
			$query = ' (*:* uid:' . intval($id) . ' AND type:pages) OR (*:* pid:' . intval($id) . ' AND NOT type:pages)';
			$offset = 0;
			$limit = 100;
			$params = array('qt' => 'standard');
			$solrid = t3lib_div::_GP('solrid');
			$solrAdminConnection->setCurrentUrl(t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'index.php?id=' . $id);
			if (!empty($solrid)) {
				$response = $solrAdminConnection->search('id:' . $solrAdminConnection->escape($solrid), 0, 1000, $params);
				$content .= $solrAdminConnection->renderRecord($response);
			} else {
				$response = $solrAdminConnection->search($query, $offset, $limit, $params);
				if (intval($response->response->numFound) === 0) {
					$content .= $GLOBALS['LANG']->getLL('nodata');
				} else {
					$content .= $solrAdminConnection->renderRecords($response, array('id', 'title', 'indexed'));
				}
			}
		} else {
			$content .= $GLOBALS['LANG']->getLL('nosolrconf');
		}
		return $this->pObj->doc->spacer(5) . $this->pObj->doc->section('Solr Admin', $content, 0, 1);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solradmin/modfunc1/class.tx_solradmin_modfunc1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solradmin/modfunc1/class.tx_solradmin_modfunc1.php']);
}

?>