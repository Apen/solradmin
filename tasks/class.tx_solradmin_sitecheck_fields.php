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

class tx_solradmin_sitecheck_fields implements tx_scheduler_AdditionalFieldProvider
{

	/**
	 * Generate the html code of the fields
	 *
	 * @param array               $taskInfo
	 * @param object              $task
	 * @param tx_scheduler_Module $parentObject
	 * @return array
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {
		$additionalFields = array();
		$additionalFields['site'] = $this->generateInputField('site', $taskInfo, $task, $parentObject);
		$additionalFields['limit'] = $this->generateInputField('limit', $taskInfo, $task, $parentObject);
		$additionalFields['delete'] = $this->generateCheckboxField('delete', $taskInfo, $task, $parentObject);
		return $additionalFields;
	}

	/**
	 * Generate an input field for the scheduler
	 *
	 * @param string              $fieldId
	 * @param array               $taskInfo
	 * @param object              $task
	 * @param tx_scheduler_Module $parentObject
	 * @return array
	 */
	public function generateInputField($fieldId, &$taskInfo, $task, tx_scheduler_Module $parentObject) {
		if (empty($taskInfo['sitecheck_' . $fieldId])) {
			if ($parentObject->CMD == 'edit') {
				$taskInfo['sitecheck_' . $fieldId] = $task->$fieldId;
			} else {
				$taskInfo['sitecheck_' . $fieldId] = '';
			}
		}
		$value = htmlentities($taskInfo['sitecheck_' . $fieldId]);
		$fieldCode = '<input type="text" name="tx_scheduler[sitecheck_' . $fieldId . ']" id="' . $fieldId . '" value="' . $value . '" size="50" />';
		return array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:solradmin/tasks/locallang.xml:' . $fieldId,
			'cshKey'   => '',
			'cshLabel' => $fieldId
		);
	}

	/**
	 * Generate a textarea field for the scheduler
	 *
	 * @param string              $fieldId
	 * @param array               $taskInfo
	 * @param object              $task
	 * @param tx_scheduler_Module $parentObject
	 * @return array
	 */
	public function generateTextField($fieldId, &$taskInfo, $task, tx_scheduler_Module $parentObject) {
		if (empty($taskInfo['sitecheck_' . $fieldId])) {
			if ($parentObject->CMD == 'edit') {
				$taskInfo['sitecheck_' . $fieldId] = $task->$fieldId;
			} else {
				$taskInfo['sitecheck_' . $fieldId] = '';
			}
		}
		$value = htmlentities($taskInfo['sitecheck_' . $fieldId]);
		$fieldCode = '<textarea type="text" name="tx_scheduler[sitecheck_' . $fieldId . ']" id="' . $fieldId . '" cols="50" rows="10">' . $value . '</textarea>';
		return array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:solradmin/tasks/locallang.xml:' . $fieldId,
			'cshKey'   => '',
			'cshLabel' => $fieldId
		);
	}

	/**
	 * Generate a checkbox field for the scheduler
	 *
	 * @param string              $fieldId
	 * @param array               $taskInfo
	 * @param object              $task
	 * @param tx_scheduler_Module $parentObject
	 * @return array
	 */
	public function generateCheckboxField($fieldId, &$taskInfo, $task, tx_scheduler_Module $parentObject) {
		if (empty($taskInfo['sitecheck_' . $fieldId])) {
			if ($parentObject->CMD == 'edit') {
				$taskInfo['sitecheck_' . $fieldId] = $task->$fieldId;
			} else {
				$taskInfo['sitecheck_' . $fieldId] = '';
			}
		}
		$checked = '';
		if ($task->$fieldId == 1) {
			$checked = 'checked="checked"';
		}
		$fieldCode = '<input type="checkbox" value="1" name="tx_scheduler[sitecheck_' . $fieldId . ']" id="' . $fieldId . '" ' . $checked . '/>';
		return array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:solradmin/tasks/locallang.xml:' . $fieldId,
			'cshKey'   => '',
			'cshLabel' => $fieldId
		);
	}

	/**
	 * Validate the fields
	 *
	 * @param array               $submittedData
	 * @param tx_scheduler_Module $parentObject
	 * @return bool
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$result = TRUE;

		if (empty($submittedData['sitecheck_limit']) === TRUE) {
			$parentObject->addMessage(
				$GLOBALS['LANG']->sL('LLL:EXT:solradmin/tasks/locallang.xml:errorfields'), t3lib_FlashMessage::ERROR
			);
			$result = FALSE;
		}

		if (empty($submittedData['sitecheck_site']) === TRUE) {
			$parentObject->addMessage(
				$GLOBALS['LANG']->sL('LLL:EXT:solradmin/tasks/locallang.xml:errorfields'), t3lib_FlashMessage::ERROR
			);
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Save date form the fields
	 *
	 * @param array             $submittedData
	 * @param tx_scheduler_Task $task
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->limit = $submittedData['sitecheck_limit'];
		$task->site = $submittedData['sitecheck_site'];
		$task->delete = $submittedData['sitecheck_delete'];
	}
}

?>
