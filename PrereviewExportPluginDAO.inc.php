<?php

/**
 * @file plugins/importexport/prereview/PrereviewExportPluginDAO.inc.php
 *
 * @class PrereviewPluginDAO
 * @ingroup plugins.importexport.prereview
 *
 */


class PrereviewExportPluginDAO extends DAO {

	var $_result;

	var $_loadId;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->_result = false;
		$this->_loadId = null;
	}

	function getDataPrereview($id) {
		$result = $this->retrieve(
			'SELECT setting_value, status
			FROM prereview_settings WHERE publication_id = ?',
			array((int) $id) 
		);
		$returner = $result->current();
		return $returner;
	}
	function getAllData($contextId) {
		$result = $this->retrieve(
		'SELECT submissions.submission_id id, publication_settings.setting_value doi, prereview_settings.setting_value prereview, prereview_settings.status prereview_status
		 FROM submissions
		 INNER JOIN publications
		 INNER JOIN publication_settings
		 INNER JOIN prereview_settings
		 WHERE submissions.current_publication_id = publications.publication_id 
		 AND publications.publication_id = publication_settings.publication_id 
		 AND submissions.submission_id = prereview_settings.publication_id 
		 AND publication_settings.setting_name="pub-id::doi" 
		 AND context_id = ? AND submissions.status = 3',
			array((int) $contextId) 
		);
		
		return $result;
	}

	function updateStatus($status, $id) {
		
		$this->update(
			'UPDATE	prereview_settings
			SET	status = ?
			WHERE publication_id = ?',
			array(
				$status,
				$id,
								
			)
		);
		return true;
	}
	
	
}

