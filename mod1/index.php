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

$LANG->includeLLFile('EXT:solradmin/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.


/**
 * Module 'Solr Admin' for the 'solradmin' extension.
 *
 * @author    CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage    tx_solradmin
 */
class  tx_solradmin_module1 extends t3lib_SCbase
{
	protected $pageinfo;
	protected $nbElementsPerPage = 15;

	/**
	 * Initializes the Module
	 * @return    void
	 */

	public function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Check nb per page
		$nbPerPage = t3lib_div::_GP('nbPerPage');
		if ($nbPerPage !== null) {
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
				'1' => $LANG->getLL('function1'),
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
			$this->doc->bodyTagAdditions = ' style="height:95%;"';
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

			$headerSection = $this->doc->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']) . '<br />' . $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path') . ': ' . t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'], 50);
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// TODO make a menu to select the connection
			/*
			$solrConnections = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getAllConnections();
			$selectSolr = '<select name="solrconnections">';
			foreach ($solrConnections as $solrConnection) {
				$selectSolr .= '<option value="">' . $solrConnection->getScheme() . '://' . $solrConnection->getHost() . ':' . $solrConnection->getPort() . $solrConnection->getPath() . '</option>';
			}
			$selectSolr .= '</select>';
			*/

			$this->content .= $this->doc->section('', $this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
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

		// get the first connection
		// TODO get the selected connection
		$this->solrConnection = $solrConnections[0];
		$this->currentConnection['scheme'] = $solrConnections[0]->getScheme();
		$this->currentConnection['host'] = $solrConnections[0]->getHost();
		$this->currentConnection['port'] = $solrConnections[0]->getPort();
		$this->currentConnection['path'] = $solrConnections[0]->getPath();

		switch ((string)$this->MOD_SETTINGS['function']) {
			case 1:
				$this->displaySolrModule();
				break;
			case 2:
				$this->displaySearchRecords();
				break;
			case 3:
				$this->displayTypo3SolrAdmin();
				break;
		}
	}

	public function displaySolrModule() {
		$this->content .= '<input type="button" value ="' . $GLOBALS['LANG']->getLL('iframeback') . '" onclick="history.go(-1)"/><br/><br/><iframe src="' . $this->currentConnection['scheme'] . '://' . $this->currentConnection['host'] . ':' . $this->currentConnection['port'] . $this->currentConnection['path'] . 'admin/" style="width:100%;height:600px;border:0px;"></iframe>';
	}

	public function displaySearchRecords() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		$delete = t3lib_div::_GP('delete');
		if (!empty($delete)) {
			$this->solrConnection->commit();
			$this->solrConnection->deleteByQuery('id:' . $delete);
			$this->solrConnection->commit();
		}
		$pointer = t3lib_div::_GP('pointer');
		$query = t3lib_div::_GP('query');
		$solrfields = t3lib_div::_GP('solrfields');
		$offset = ($pointer !== null) ? intval($pointer) : 0;
		$limit = $this->nbElementsPerPage;
		$fields = ($solrfields !== null) ? $solrfields : array('id', 'site', 'title', 'created', 'url');
		$actionURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		if (empty($query)) {
			$query = '*:*';
		}
		$actionURL .= '&query=' . $query;
		$search = $this->solrConnection->search($query, $offset, $limit);
		$response = json_decode($search->getRawResponse());
		$content = '';
		$content .= $GLOBALS['LANG']->getLL('query') . ' : <input type="text" name="query" value="' . $query . '" size="100"/> <input type="submit" value="' . $GLOBALS['LANG']->getLL('search') . '" /><br/>';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$numTotal = intval($response->response->numFound);
		$content .= $GLOBALS['LANG']->getLL('results') . ' : ' . $numTotal . ' ' . $GLOBALS['LANG']->getLL('records');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		foreach ($fields as $field) {
			$content .= '<td class="cell" align="center">' . strtoupper($field) . '</td>';
		}
		$content .= '<td class="cell" align="center">&nbsp;</td>';
		$content .= '</tr>';
		foreach ($response->response->docs as $doc) {
			$content .= '<tr class="db_list_normal">';
			foreach ($fields as $field) {
				if (is_array($doc->$field)) {
					$content .= '<td class="cell">' . implode('<br/>', $doc->$field) . '</td>';
				} else {
					$content .= '<td class="cell">' . $doc->$field . '</td>';
				}
			}
			$content .= '<td class="cell"><a onclick="deleteRecord(\'' . $actionURL . '&delete=' . $doc->id . '\');"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/garbage.gif"/></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		$this->content .= $content . '<br/>';
		$this->content .= $this->renderListNavigation($numTotal, $this->nbElementsPerPage, $pointer) . '<br/>';
		$this->content .= $this->getSelectFields($fields) . '&nbsp;&nbsp;';
		$this->content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('filter') . '" /><br/><br/><br/>';
	}

	public function getSelectFields($selectedList) {
		$content = '';
		$solrFields = $this->solrConnection->getFieldsMetaData();
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
		if (!empty($solraction)) {
			switch ($solraction) {
				case 'emptyIndex':
					$this->solrConnection->commit();
					$this->solrConnection->deleteByQuery('*:*');
					$this->solrConnection->commit();
					break;
				case 'commit':
					$this->solrConnection->commit();
					break;
				case 'optimize':
					$this->solrConnection->optimize();
					break;
			}
		}
		$content = '';
		$content .= '<input type="hidden" id="solraction" name="solraction" value="" />';
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('empty') . '" name="s_emptyIndex" onclick="Check = confirm(\'' . $GLOBALS['LANG']->getLL('areyousure') . '\'); if (Check == true) document.forms[0].solraction.value=\'emptyIndex\';" /><br /><br />';
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('commit') . '" name="s_commitPendingDocuments" onclick="document.forms[0].solraction.value=\'commit\';" /><br /><br />';
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('optimize') . '" name="s_optimizeIndex" onclick="document.forms[0].solraction.value=\'optimize\';" /><br /><br />';
		$this->content .= $content;
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
		$solrfields = t3lib_div::_GP('solrfields');
		$listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txsolradminM1';
		$listURL .= '&nbPerPage=' . $this->nbElementsPerPage;
		if (!empty($query)) {
			$listURL .= '&query=' . $query;
		}
		if (!empty($query)) {
			$i = 0;
			foreach ($solrfields as $solrfield) {
				$listURL .= '&solrfields[' . $i++ . ']=' . $solrfield;
			}
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
