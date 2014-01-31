<?php
/**
 * Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Utils Plugin
 *
 * Utils Csv Import Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class CsvImportBehavior extends ModelBehavior {

/**
 * Importable behavior settings
 *
 * @var array
 */
	public $settings = array();

/**
 * List of errors generated by the import action
 *
 * @var array
 */
	public $errors = array();

/**
 * List of objects instances or callables to notify from events on this class
 *
 * @var array
 */
	protected $_subscribers = array();

/**
 * Initializes this behavior for the model $Model
 *
 * @param Model $Model
 * @param array $settings
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = array(
				'delimiter' => ';',
				'enclosure' => '"',
				'hasHeader' => false
			);
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
	}

/**
 * Returns a line form the CSV file and advances the pointer to the next one
 *
 * @param Model $Model
 * @param SplFileObject $handle CSV file handler
 * @return array list of attributes fetched from the CSV file
 */
	protected function _getCSVLine(Model &$Model, SplFileObject $handle) {
		if ($handle->eof()) {
			return false;
		}
		return $handle->fgetcsv(
			$this->settings[$Model->alias]['delimiter'],
			$this->settings[$Model->alias]['enclosure']
		);
	}

/**
 * Returns a list of keys representing the columns of the CSV file
 *
 * @param Model $Model
 * @param SplFileObject $handle CSV file handler
 * @return array list of attributes fetched from the CSV file
 */
	protected function _getHeader(Model &$Model, SplFileObject $handle) {
		if ($this->settings[$Model->alias]['hasHeader'] === true) {
			$header = $this->_getCSVLine($Model, $handle);
		} else {
			//$header = array_keys($Model->schema());
			$header = array('Contact.first_name','Contact.last_name','Contact.contact_status_id','Contact.company','Contact.city','Contact.phone','Contact.email');
		}
		return $header;
	}
	/**
	 * Returns a list of keys representing the columns of the CSV file
	 *
	 * @param Model $Model
	 * @param string $file path to the CSV file
	 * @param array $fixed data to be merged with every row
	 * @param boolean $returnSaved true to return
	 * @throws RuntimeException if $file does not exists
	 * @return mixed boolean indicating the success of the operation or list of saved records
	 */
	public function importCSVdata(Model &$Model, $file, $fixed = array('Contact'=>array('user_id'=>1)), $returnSaved = false) {
		$handle = new SplFileObject($file, 'rb');
		$header = $this->_getHeader($Model, $handle);
		//$db = $Model->getDataSource();
		//$db->begin($Model);
		$saved = array();
		$allData = array();
		
		$i = 0;
		while (($row = $this->_getCSVLine($Model, $handle)) !== false) {
			$data = array();
			foreach ($header as $k => $col) {
				// get the data field from Model.field
				if (strpos($col, '.') !== false) {
					$keys = explode('.', $col);
					if (isset($keys[2])) {
						$data[$keys[0]][$keys[1]][$keys[2]]= (isset($row[$k])) ? $row[$k] : '';
					} else {
						$data[$keys[0]][$keys[1]]= (isset($row[$k])) ? $row[$k] : '';
					}
				} else {
					$data[$Model->alias][$col]= (isset($row[$k])) ? $row[$k] : '';
				}
			}
	
			$data = Set::merge($data, $fixed);
			//$Model->create();
			//$Model->id = isset($data[$Model->alias][$Model->primaryKey]) ? $data[$Model->alias][$Model->primaryKey] : false;
	
			//beforeImport callback
			//if (method_exists($Model, 'beforeImport')) {
				//$data = $Model->beforeImport($data);
			//}
			//echo '<pre>'; print_r($data); echo '</pre>';
			$error = false;/*
			$Model->set($data);
			if (!$Model->validates()) {
			$this->errors[$Model->alias][$i]['validation'] = $Model->validationErrors;
			$error = true;
			$this->_notify($Model, 'onImportError', $this->errors[$Model->alias][$i]);
	
			}
			*/
			// save the row
			/*
			if (!$error && !$Model->saveAll($data, array('validate' => false,'atomic' => false))) {
				$this->errors[$Model->alias][$i]['save'] = sprintf(__d('utils', '%s for Row %d failed to save.'), $Model->alias, $i);
				$error = true;
				$this->_notify($Model, 'onImportError', $this->errors[$Model->alias][$i]);
	
			}
	*/
			if ($data['Contact']['contact_status_id'])
			{
				$allData[] = $data;
			}
			
			if (!$error) {
				$this->_notify($Model, 'onImportRow', $data);
				if ($returnSaved) {
					$saved[] = $i;
				}
			}
	
			$i++;
		}
	
		$success = empty($this->errors);
		if (!$success) {
			//$db->rollback($Model);
			return false;
		}
	
		//$db->commit($Model);
	/*
		if ($returnSaved) {
			return $saved;
		}
	*/
		return $allData;
	}
/**
 * Returns a list of keys representing the columns of the CSV file
 *
 * @param Model $Model
 * @param string $file path to the CSV file
 * @param array $fixed data to be merged with every row
 * @param boolean $returnSaved true to return
 * @throws RuntimeException if $file does not exists
 * @return mixed boolean indicating the success of the operation or list of saved records
 */
	public function importCSV(Model &$Model, $file, $fixed = array(), $returnSaved = false) {
		$handle = new SplFileObject($file, 'rb');
		$header = $this->_getHeader($Model, $handle);
		$db = $Model->getDataSource();
		$db->begin($Model);
		$saved = array();
		$i = 0;
		while (($row = $this->_getCSVLine($Model, $handle)) !== false) {
			$data = array();
			foreach ($header as $k => $col) {
				// get the data field from Model.field
				if (strpos($col, '.') !== false) {
					$keys = explode('.', $col);
					if (isset($keys[2])) {
						$data[$keys[0]][$keys[1]][$keys[2]]= (isset($row[$k])) ? $row[$k] : '';
					} else {
						$data[$keys[0]][$keys[1]]= (isset($row[$k])) ? $row[$k] : '';
					}
				} else {
					$data[$Model->alias][$col]= (isset($row[$k])) ? $row[$k] : '';
				}
			}

			$data = Set::merge($data, $fixed);
			$Model->create();
			$Model->id = isset($data[$Model->alias][$Model->primaryKey]) ? $data[$Model->alias][$Model->primaryKey] : false;

			//beforeImport callback
			if (method_exists($Model, 'beforeImport')) {
				$data = $Model->beforeImport($data);
			}
//echo '<pre>'; print_r($data); echo '</pre>';
			$error = false;/*
			$Model->set($data);
			if (!$Model->validates()) {
				$this->errors[$Model->alias][$i]['validation'] = $Model->validationErrors;
				$error = true;
				$this->_notify($Model, 'onImportError', $this->errors[$Model->alias][$i]);
				
			}
*/
			// save the row
			if (!$error && !$Model->saveAll($data, array('validate' => false,'atomic' => false))) {
				$this->errors[$Model->alias][$i]['save'] = sprintf(__d('utils', '%s for Row %d failed to save.'), $Model->alias, $i);
				$error = true;
				$this->_notify($Model, 'onImportError', $this->errors[$Model->alias][$i]);
				
			}

			if (!$error) {
				$this->_notify($Model, 'onImportRow', $data);
				if ($returnSaved) {
					$saved[] = $i;
				} 
			}

			$i++;
		}

		$success = empty($this->errors);
		if (!$returnSaved && !$success) {
			$db->rollback($Model);
			return false;
		}

		$db->commit($Model);

		if ($returnSaved) {
			return $saved;
		}

		return true;
	}

/**
 * Returns the errors generated by last import
 *
 * @param Model $Model
 * @return array
 */
	public function getImportErrors(Model &$Model) {
		if (empty($this->errors[$Model->alias])) {
			return array();
		}
		return $this->errors[$Model->alias];
	}

/**
 * Attachs a new listener for the events generated by this class
 *
 * @param Model $Model
 * @param mixed listener instances of an object or valid php callback
 * @return void
 */
	public function attachImportListener(Model $Model, $listener) {
		$this->_subscribers[$Model->alias][] = $listener;
	}

/**
 * Notifies the listeners of events generated by this class
 *
 * @param Model $Model
 * @param string $action the name of the event. It will be used as method name for object listeners
 * @param mixed $data additional information to pass to the listener callback
 * @return void
 */
	protected function _notify(Model $Model, $action, $data = null) {
		if (empty($this->_subscribers[$Model->alias])) {
			return;
		}
		foreach ($this->_subscribers[$Model->alias] as $object) {
			if (method_exists($object, $action)) {
				$object->{$action}($data);
			}
			if (is_callable($object)) {
				call_user_func($object, $action, $data);
			}
		}
	}
}
