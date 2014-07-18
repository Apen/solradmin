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
class tx_solradmin_connection {
	protected $solrConnection = NULL;
	protected $scheme = '';
	protected $host = '';
	protected $port = '';
	protected $path = '';
	protected $currentUrl = '';

	/**
	 * constructor for class tx_solradmin_connection
	 */

	public function __construct($solrConnection) {
		$GLOBALS['LANG']->includeLLFile('EXT:solradmin/mod1/locallang.xml');
		$this->solrConnection = $solrConnection;
		$this->scheme = $solrConnection->getScheme();
		$this->host = $solrConnection->getHost();
		$this->port = $solrConnection->getPort();
		$this->path = $solrConnection->getPath();
	}

	public function getSolrAdminUrl() {
		return $this->scheme . '://' . $this->host . ':' . $this->port . $this->path . 'admin/';
	}

	public function search($query, $offset = 0, $limit = 10, $params = array()) {
		$search = $this->solrConnection->search($query, $offset, $limit, $params);
		$response = json_decode($search->getRawResponse());
		return $response;
	}

	public function searchUrl($query, $offset = 0, $limit = 10, $params = array(), $xml = FALSE) {
		if ($xml === FALSE) {
			$params['wt'] = Apache_Solr_Service::SOLR_WRITER;
			$params['json.nl'] = Apache_Solr_Service::NAMED_LIST_MAP;
		} else {
			$params['wt'] = 'xml';
		}
		$params['q'] = $query;
		$params['start'] = $offset;
		$params['rows'] = $limit;
		$params['qt'] = 'standard';
		$queryString = $this->_generateQueryString($params);
		return $this->scheme . '://' . $this->host . ':' . $this->port . $this->path . 'select?' . $queryString;
	}

	public function renderRecords($response, array $fields) {
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$numTotal = intval($response->response->numFound);
		$content .= $GLOBALS['LANG']->getLL('results') . ' : ' . $numTotal . ' ' . $GLOBALS['LANG']->getLL('records');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		foreach ($fields as $field) {
			$content .= '<td class="cell" align="center" valign="middle"><strong>' . strtoupper($field) . ':</strong></td>';
		}
		$content .= '<td class="cell" align="center">&nbsp;</td>';
		$content .= '</tr>';
		foreach ($response->response->docs as $doc) {
			$content .= '<tr class="db_list_normal">';
			foreach ($fields as $field) {
				if (is_array($doc->$field)) {
					$content .= '<td class="cell">' . implode('<br/>', $doc->$field) . '</td>';
				} else {

					switch ($field) {
						case 'id':
							$content .= '<td class="cell">' . $doc->$field . '';
							$content .= '<a href="' . $this->currentUrl . '&solrid=' . $doc->$field . '">&nbsp;&nbsp;<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL'
								) . 'typo3/sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
							break;
						case 'url':
							$content .= '<td class="cell">' . $doc->$field . '';
							$currentUrl = $doc->$field;
							if (strpos($currentUrl, 'http') === 0) {
								$content .= '<a href="' . $currentUrl . '" target="_blank">&nbsp;&nbsp;<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL'
									) . 'typo3/sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
							} else {
								if (!empty($doc->site) && !empty($doc->url)) {
									if (strpos($currentUrl, 'http') === FALSE) {
										$doc->site = 'http://' . $doc->site . '/';
									}
									$content .= '<a href="' . $doc->site . $currentUrl . '" target="_blank">&nbsp;&nbsp;<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR'
										) . 'sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
								} else {
									$content .= '</td>';
								}
							}
							break;
						default:
							$content .= '<td class="cell">' . $doc->$field . '</td>';
							break;
					}
				}
			}
			$content .= '<td class="cell"><a onclick="deleteRecord(\'' . $this->currentUrl . '&delete=' . $doc->id . '\');"><img style="cursor:pointer;" src="' . t3lib_div::getIndpEnv('TYPO3_SITE_PATH'
				) . 'typo3/sysext/t3skin/icons/gfx/garbage.gif"/></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function renderRecord($response) {
		$content = '';
		$content .= '<a href="' . $this->currentUrl . '"><-- ' . $GLOBALS['LANG']->getLL('back') . '</a>';
		foreach ($response->response->docs as $doc) {
			foreach ($doc as $field => $fieldValue) {
				$content .= '<h4>' . $field . '</h4>';
				if (is_array($fieldValue)) {
					$content .= '<p>' . self::viewArray($fieldValue) . '</p>';
				} else {
					$content .= '<p>' . $fieldValue . '</p>';
				}
			}
		}
		$content .= '<a href="' . $this->currentUrl . '"><-- ' . $GLOBALS['LANG']->getLL('back') . '</a>';
		return $content;
	}

	public function checkDelete() {
		$delete = t3lib_div::_GP('delete');
		if (!empty($delete)) {
			$delete = $this->escape($delete);
			$this->solrConnection->commit();
			$this->solrConnection->deleteByQuery('id:' . $delete);
			$this->solrConnection->commit();
		}
	}

	public static function escape($value) {
		//list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
		$pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
		$replace = '\\\$1';
		return preg_replace($pattern, $replace, $value);
	}

	public function escapeUrlvalue($value) {
		$pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\?|:|\\\)/';
		$replace = '\\\$1';
		return preg_replace($pattern, $replace, $value);
	}

	protected function _generateQueryString($params) {
		if (version_compare(phpversion(), '5.1.3', '<')) {
			$queryString = http_build_query($params, NULL, $this->_queryStringDelimiter);
			return preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);
		} else {
			$queryString = http_build_query($params);
			return preg_replace('/\\[(?:[0-9]|[1-9][0-9]+)\\]=/', '=', $queryString);
		}
	}

	public function setCurrentUrl($currentUrl) {
		$currentUrl = str_replace('http\:', 'http\\\:', $currentUrl);
		if (version_compare(TYPO3_version, '6.2.0', '>=')) {
			$currentUrl .= '&moduleToken=' . \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->generateToken('moduleCall', 'tools_txsolradminM1');
		}
		$this->currentUrl = $currentUrl;
	}

	public function getSolrConnection() {
		return $this->solrConnection;
	}

	/**
	 * Central method for making a post operation against this Solr Server
	 *
	 * @param string $url
	 * @param string $rawPost
	 * @param float  $timeout Read timeout in seconds
	 * @param string $contentType
	 * @return Apache_Solr_Response
	 *
	 * @throws Apache_Solr_HttpTransportException If a non 200 response status is returned
	 */
	public function add($rawPost) {
		$responde = $this->solrConnection->add($rawPost);
		$this->solrConnection->commit();
		return $responde;
	}

	/**
	 * Returns HTML-code, which is a visual representation of a multidimensional array
	 * use t3lib_div::print_array() in order to print an array
	 * Returns false if $array_in is not an array
	 *
	 * @param    mixed        Array to view
	 * @return    string        HTML output
	 */
	public static function viewArray($array_in) {
		if (is_array($array_in)) {
			$result = '<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
			if (count($array_in) == 0) {
				$result .= '<tr><td><font face="Verdana,Arial" size="1"><strong>EMPTY!</strong></font></td></tr>';
			} else {
				foreach ($array_in as $key => $val) {
					$result .= '<tr><td valign="top"><font face="Verdana,Arial" size="1">' . htmlspecialchars((string)$key) . '</font></td><td>';
					if (is_array($val)) {
						$result .= self::viewArray($val);
					} elseif (is_object($val)) {
						$string = '';
						if (method_exists($val, '__toString')) {
							$string .= get_class($val) . ': ' . (string)$val;
						} else {
							$string .= print_r($val, TRUE);
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' . nl2br(htmlspecialchars($string)) . '<br /></font>';
					} else {
						if (gettype($val) == 'object') {
							$string = 'Unknown object';
						} else {
							$string = (string)$val;
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' . nl2br(htmlspecialchars($string)) . '<br /></font>';
					}
					$result .= '</td>
					</tr>';
				}
			}
			$result .= '</table>';
		} else {
			$result = '<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">
				<tr>
					<td><font face="Verdana,Arial" size="1" color="red">' .
				nl2br(htmlspecialchars((string)$array_in)) .
				'<br /></font></td>
			</tr>
		</table>'; // Output it as a string.
		}
		return $result;
	}
}

?>