<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 CERDAN Yohann <cerdanyohann@yahoo.fr>
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

$LANG->includeLLFile('EXT:solradmin/mod1/locallang.xml');
if (version_compare(TYPO3_version, '6.2.0', '<')) {
	require_once(PATH_t3lib . 'class.t3lib_scbase.php');
}
require_once(PATH_site . 'typo3conf/ext/solradmin/classes/class.tx_solradmin_connection.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.


/**
 * Module 'Solr Admin' for the 'solradmin' extension.
 *
 * @author        CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package       TYPO3
 * @subpackage    tx_solradmin
 */
class  tx_solradmin_module1 extends t3lib_SCbase {
	protected $pageinfo;
	protected $nbElementsPerPage = 15;
	protected $beUserSessionDatas = NULL;

	/**
	 * Initializes the Module
	 *
	 * @return    void
	 */

	public function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Check nb per page
		$nbPerPage = t3lib_div::_GP('nbPerPage');
		if ($nbPerPage !== NULL) {
			$this->nbElementsPerPage = $nbPerPage;
		}
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */

	public function menuConfig() {
		global $LANG;
		$this->MOD_MENU = Array(
			'function' => Array(
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

	public function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->divClass = '';
			$this->doc->bodyTagAdditions = ' style="height:95%;margin: 0px 10px;"';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

			// JavaScript
			$this->doc->JScode = '
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

			$this->doc->postCode = '
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = '';
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			//$this->content .= $this->doc->spacer(5);

			// multi core connections
			$beUserSession = $GLOBALS['BE_USER']->fetchUserSession();
			$this->beUserSessionDatas = unserialize($beUserSession['ses_data']);
			$gpSolrConnections = t3lib_div::_GP('solrconnections');
			if ($gpSolrConnections !== NULL) {
				$GLOBALS['BE_USER']->setAndSaveSessionData('indexsolrconnection', $gpSolrConnections);
				$this->beUserSessionDatas['indexsolrconnection'] = $gpSolrConnections;
			} else {
				if (empty($this->beUserSessionDatas['indexsolrconnection'])) {
					$GLOBALS['BE_USER']->setAndSaveSessionData('indexsolrconnection', 0);
					$this->beUserSessionDatas['indexsolrconnection'] = 0;
				} else {
					$gpSolrConnections = $this->beUserSessionDatas['indexsolrconnection'];
					$this->beUserSessionDatas['indexsolrconnection'] = $gpSolrConnections;
				}
			}
			$solrConnections = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getAllConnections();
			if (version_compare(TYPO3_version, '6.2.0', '>=')) {
				$token = '&moduleToken=' . \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->generateToken('moduleCall', 'tools_txsolradminM1');
			}

			$selectSolr = '<select name="solrconnections" onchange="jumpToUrl(\'mod.php?&amp;id=0' . $token . '&amp;M=tools_txsolradminM1&amp;solrconnections=\'+this.options[this.selectedIndex].value,this);">';
			$index = 0;
			foreach ($solrConnections as $solrConnection) {
				if ($gpSolrConnections == $index) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				}
				$selectSolr .= '<option value="' . $index . '"' . $selected . '>' . $solrConnection->getScheme() . '://' . $solrConnection->getHost() . ':' . $solrConnection->getPort() . $solrConnection->getPath() . ' [' . $index . ']</option>';
				$index++;
			}
			$selectSolr .= '</select>';

			$this->content .= $this->doc->section('', $this->doc->funcMenu($headerSection,
			                                                               t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']
			                                                               ) . $selectSolr
			                                        )
			);
			$this->content .= $this->doc->divider(5);
			$this->moduleContent();
		} else {
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}

	}

	/**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */

	public function printContent() {
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return    void
	 */

	public function moduleContent() {
		$solrConnections = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getAllConnections();

		// get the selected connection
		$this->solrAdminConnection = new tx_solradmin_connection($solrConnections[$this->beUserSessionDatas['indexsolrconnection']]);

		switch ((string)$this->MOD_SETTINGS['function']) {
			case 2:
				$this->displaySearchRecords();
				break;
			case 3:
				$this->displayTypo3SolrAdmin();
				break;
		}
	}

	public function displaySearchRecords() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		// check delete
		$this->solrAdminConnection->checkDelete();

		// url params
		$pointer = t3lib_div::_GP('pointer');
		$query = t3lib_div::_GP('query');
		$urlquery = t3lib_div::_GP('urlquery');
		$solrfields = t3lib_div::_GP('solrfields');
		$offset = ($pointer !== NULL) ? intval($pointer) : 0;
		$limit = $this->nbElementsPerPage;
		$fields = ($solrfields !== NULL) ? $solrfields : array('id', 'site', 'title', 'indexed', 'url');
		$baseActionURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		if (empty($query)) {
			$query = '*:*';
		}
		if (empty($urlquery)) {
			$urlquery = '';
		} else {
			$query = 'url:' . $this->solrAdminConnection->escapeUrlvalue($urlquery);
		}
		$actionURL = $baseActionURL . '&query=' . urlencode($query) . '&nbPerPage=' . $limit;
		$params = array('qt' => 'standard');

		$solrfields = t3lib_div::_GP('solrfields');
		if (!empty($solrfields)) {
			$i = 0;
			foreach ($solrfields as $solrfield) {
				$actionURL .= '&solrfields[' . $i++ . ']=' . $solrfield;
			}
		}

		if (!empty($pointer)) {
			$actionURL .= '&pointer=' . $pointer;
		}

		$this->solrAdminConnection->setCurrentUrl($actionURL);

		$solrid = t3lib_div::_GP('solrid');

		if (!empty($solrid)) {
			// search
			$response = $this->solrAdminConnection->search('id:' . $this->solrAdminConnection->escape($solrid), 0, 1000, $params);

			// single view
			$this->content .= $this->solrAdminConnection->renderRecord($response);
		} else {
			// search
			$response = $this->solrAdminConnection->search($query, $offset, $limit, $params);

			// table view
			$content = '';
			$content .= $GLOBALS['LANG']->getLL('query') . ' : <input type="text" name="query" value="' . htmlspecialchars($query) . '" size="30"/>&nbsp;&nbsp;';
			$content .= $GLOBALS['LANG']->getLL('or') . ' URL : <input type="text" name="urlquery" value="' . htmlspecialchars($urlquery) . '" size="30"/>&nbsp;&nbsp;';
			$content .= '<input type="hidden" name="pointer" value="0" />&nbsp;&nbsp;';
			$content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('search') . '" />&nbsp;&nbsp;';
			$content .= '<a href="' . $baseActionURL . '&query=*:*&nbPerPage=' . $limit . '"><strong>Reset</strong></a>';
			$content .= '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . $this->solrAdminConnection->searchUrl($query, $offset, $limit, $params
				) . '" target="_blank"><strong>' . $GLOBALS['LANG']->getLL('openjson') . '</strong></a>';
			$content .= '&nbsp;&nbsp;<a href="' . $this->solrAdminConnection->searchUrl($query, $offset, $limit, $params, TRUE) . '" target="_blank"><strong>' . $GLOBALS['LANG']->getLL('openxml'
				) . '</strong></a>';
			$content .= '&nbsp;&nbsp;<a href="' . $this->solrAdminConnection->getSolrAdminUrl() . '" target="_blank"><strong>' . $GLOBALS['LANG']->getLL('opensolradmin') . '</strong></a>';
			$content .= $this->solrAdminConnection->renderRecords($response, $fields);
			$this->content .= $content . '<br/>';

			// page browser
			$this->content .= $this->renderListNavigation(intval($response->response->numFound), $this->nbElementsPerPage, $pointer) . '<br/>';

			// select fields
			$this->content .= $this->getSelectFields($fields) . '&nbsp;&nbsp;';
			$this->content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('filter') . '" /><br/><br/><br/>';
		}
	}

	protected function getSelectFields($selectedList) {
		$content = '';
		$solrFields = $this->solrAdminConnection->getSolrConnection()->getFieldsMetaData();
		$solrFieldsList = array_keys(get_object_vars($solrFields));
		$content .= '<select name="solrfields[]" multiple="multiple" size="10" style="width:300px;">';
		foreach ($solrFieldsList as $solrField) {
			if (in_array($solrField, $selectedList)) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$content .= '<option value="' . $solrField . '"' . $selected . '>' . $solrField . '</option>';
		}
		$content .= '</select>';
		return $content;

	}

	public function displayTypo3SolrAdmin() {
		$solraction = t3lib_div::_GP('solraction');
		$postdatas = t3lib_div::_GP('postdatas');

		if (!empty($solraction)) {
			switch ($solraction) {
				case 'emptyIndex':
					$this->solrAdminConnection->getSolrConnection()->commit();
					$this->solrAdminConnection->getSolrConnection()->deleteByQuery('*:*');
					$this->solrAdminConnection->getSolrConnection()->commit();
					break;
				case 'commit':
					$this->solrAdminConnection->getSolrConnection()->commit();
					break;
				case 'optimize':
					$this->solrAdminConnection->getSolrConnection()->optimize();
					break;
			}
		}

		$this->content .= '<input type="hidden" id="solraction" name="solraction" value="" />';
		$this->content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('empty') . '" name="s_emptyIndex" onclick="Check = confirm(\'' . $GLOBALS['LANG']->getLL('areyousure'
			) . '\'); if (Check == true) document.forms[0].solraction.value=\'emptyIndex\';" /><br /><br />';
		$this->content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('commit'
			) . '" name="s_commitPendingDocuments" onclick="document.forms[0].solraction.value=\'commit\';" /><br /><br />';
		$this->content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('optimize') . '" name="s_optimizeIndex" onclick="document.forms[0].solraction.value=\'optimize\';" /><br /><br />';
		$this->content .= '<h3>' . $GLOBALS['LANG']->getLL('function1') . '</h3>';
		$this->content .= '<textarea name="postdatas" cols="100" rows="20">';
		if (!empty($postdatas)) {
			$this->content .= $postdatas;
			$response = $this->solrAdminConnection->add($postdatas);
		}
		$this->content .= '</textarea>';
		if ((!empty($postdatas)) && ($response->getHttpStatus() != 200)) {
			$this->content .= '<br/><br/>Error : ' . $response->getHttpStatus() . ' : ' . $response->getHttpStatusMessage();
		}
		$this->content .= '<br/><br/><input type="submit"/>';
	}

	/**
	 * Creates a page browser for tables with many records
	 */

	public function renderListNavigation($totalItems, $iLimit, $firstElementNumber, $renderPart = 'top') {
		$totalPages = ceil($totalItems / $iLimit);

		$content = '';
		$returnContent = '';
		// Show page selector if not all records fit into one page
		$first = $previous = $next = $last = $reload = '';
		$query = t3lib_div::_GP('query');
		$urlquery = t3lib_div::_GP('urlquery');
		$solrfields = t3lib_div::_GP('solrfields');
		$listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		$listURL .= '&nbPerPage=' . $this->nbElementsPerPage;
		if (empty($urlquery)) {
			$urlquery = '';
		} else {
			$query = 'url:' . $this->solrAdminConnection->escapeUrlvalue($urlquery);
		}
		if (!empty($query)) {
			$listURL .= '&query=' . urlencode($query);
		}
		if (!empty($solrfields)) {
			$i = 0;
			foreach ($solrfields as $solrfield) {
				$listURL .= '&solrfields[' . $i++ . ']=' . $solrfield;
			}
		}
		if (version_compare(TYPO3_version, '6.2.0', '>=')) {
			$listURL .= '&moduleToken=' . \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->generateToken('moduleCall', 'tools_txsolradminM1');
		}
		$currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
		// First
		if ($currentPage > 1) {
			$labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');
			$first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="sysext/t3skin/icons/gfx/control_first.gif"></a>';
		} else {
			$first = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_first_disabled.gif">';
		}
		// Previous
		if (($currentPage - 1) > 0) {
			$labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');
			$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="sysext/t3skin/icons/gfx/control_previous.gif"></a>';
		} else {
			$previous = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_previous_disabled.gif">';
		}
		// Next
		if (($currentPage + 1) <= $totalPages) {
			$labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');
			$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="sysext/t3skin/icons/gfx/control_next.gif"></a>';
		} else {
			$next = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_next_disabled.gif">';
		}
		// Last
		if ($currentPage != $totalPages) {
			$labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');
			$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="sysext/t3skin/icons/gfx/control_last.gif"></a>';
		} else {
			$last = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_last_disabled.gif">';
		}

		$pageNumberInput = '<span>' . $currentPage . '</span>';
		$pageIndicator = '<span class="pageIndicator">'
			. sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
			. '</span>';

		if ($totalItems > ($firstElementNumber + $iLimit)) {
			$lastElementNumber = $firstElementNumber + $iLimit;
		} else {
			$lastElementNumber = $totalItems;
		}

		$rangeIndicator = '<span class="pageIndicator">'
			. sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $firstElementNumber + 1, $lastElementNumber)
			. '</span>';

		$content .= '<div id="typo3-dblist-pagination">'
			. $first . $previous
			. '<span class="bar">&nbsp;</span>'
			. $rangeIndicator . '<span class="bar">&nbsp;</span>'
			. $pageIndicator . '<span class="bar">&nbsp;</span>'
			. $next . $last
			. '</div>';

		$returnContent = $content;

		return $returnContent;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solradmin/mod1/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solradmin/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_solradmin_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();
