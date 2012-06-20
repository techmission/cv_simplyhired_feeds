<?php

/**
 * @class Class for the inserts into jobs database.
 * 
 * @todo: Separate the methods in here into the ones 
 * that should go in PDO_Ext, a Schema mixin class, and the actual JobsDb class.
 * That will make this a more generic abstraction layer.
 *
 * Requires PHP Data Objects (PDO) for connection.
 */
class JobsDB {
  /* Define constants. */

  // Default database configuration file.

  /* Record types that can be inserted/deleted. */
  const RECORDS_JOB = 0; // Job records - default.

  /* Constants for operators. */
  const OP_IN = 0;
  
  /* Constants for fields to select. */
  /* @todo: Have the build fields do this based on the schema. */
  const FIELDS_ALL = 0;  // *
  const FIELDS_GUID = 1; // just the id and guid fields
  const FIELDS_CORE = 2; // the basic fields
  const FIELDS_LOCATION = 3; // the location fields
  
  /* Constants for data types. */
  const TYPE_INT = 0;
  const TYPE_STRING = 1;
  const TYPE_BOOL = 2; // Convert to 0 or 1
  const TYPE_UNIXTIME = 3; // check if it is Unix timestamp first before inserting
  const TYPE_ARRAY = 4; // must be serialized
  const TYPE_OBJ = 5; // must be serialized
  
  /* Validation error codes. */
  const RES_ERROR_NOT_ARRAY = 1;
  const RES_ERROR_NO_MEMBERS = 2;
  const RES_ERROR_UNDEFINED_TABLE = 3;
  const RES_ERROR_WRONG_TABLE = 4;
  const RES_ERROR_WRONG_DATA = 5;

  /* Define class variables. */
  private $cfgFile; // string: the configuration file containing the connection information
  private $dbInfo; // array: connection information for database 
  private $connStr; // string: connection string

  public $isLogging = FALSE; // boolean: whether to log data
  public $isDryRun = FALSE; // boolean: whether this is just a dry run - i.e., no CUD functions executed on DB

  public $tableName; // string: the table to which to write
  public $dbh = NULL; // resource: the database handle (used to do writes and reads)

  /**
   * Constructor gets the database ready for connection.
   * Uses a config file to get the connection string.
   *
   * @param string $cfg_file
   *   Configuration file with database info. (Optional)
   * @return void
   */
  function __construct($pCfgFile = '') {
    // Check requirements and toss the exception back to the caller.
    try {
  	  $this->_checkRequirements();
    }
    catch(Exception $e) {
      throw new Exception($e->getMessage());
    }

    // Set the configuration file.
    // Uses default if there wasn't one readable passed in.
    $this->_setCfgFile($pCfgFile);

    // Set the database connection info.
    try {
      $this->_setDBInfo($this->cfgFile);
    }
    catch(Exception $e) {
      echo $e->getMessage();
    }

    // Set the database connection string.
    $this->_setConnStr();
  }
  
  /**
   * Connect to the database, using the parameters initialized in the constructor.
   * No queries can run until this has happened.
   */
  public function connect() {
  	// Use the connection string to connect to the database.
  	try {
  		$this->dbh = new PDO_Ext($this->connStr, $this->dbInfo['username'], $this->dbInfo['password']);
  		// If set to logging, log that connection was successful.
  		if($this->isLogging == TRUE) {
  			echo 'Connected to database';
  		}
  	}
  	catch(PDOException $e) {
  		echo $e->getMessage();
  	}
  	// Set the default table name (tbl_opportunities)
  	$this->tableName = $this->_lookupTableName();
  }
  
  /**
   *  Return the current connection string. 
   */
  public function getConnStr($echo = FALSE) {
  	$this->_echoOrReturn($this->connStr, $echo);
  }
  
  /**
   * Return the tables that are currently part of the jobs feeds system, with their associated record types. 
   */
  public function getTables() {
  	return array('tbl_opportunities' => self::RECORDS_JOB);
  }
  
  /**
   *  Counts the number of records in a database table.
   *  @todo: Find a more efficient way to do this. 
   */
  public function countRecords($pTableName = '') {
  	$numRows = FALSE;
  	// Connect if no database handle.
  	if($this->dbh == NULL) {
  	  $this->connect();
  	}
  	// Return FALSE if no table name has been set.
  	if(empty($this->tableName) && empty($pTableName)) {
  	  return $numRows;
  	}
  	else if(empty($this->tableName) && !empty($pTableName)) {
  	  $this->tableName = $pTableName;
  	}
  	else {
  	  try {
  	  	$lSql = 'SELECT id FROM ' . $this->tableName;
  	  	$stmt = $this->dbh->query($lSql);
  	  	$numRows = $stmt->rowCount();
  	  }
  	  catch(PDOException $e) {
  	  	echo $e->getMessage();
  	  }
  	}
  	return $numRows;
  }

  /**
   * Delete all records from a table.
   */
  public function truncate($pObjType = self::RECORDS_JOB, $pSource = 'simplyhired') {
    $lNumRows = FALSE; // Assume error condition to start.
  	// Connect if no database handle.
  	if($this->dbh == NULL) {
  	  $this->connect();
  	}
  	// Set the table name from which to delete, if not already set.
  	// Default to the jobs table.
  	if(empty($this->tableName) || $pObjType != self::RECORDS_JOB) {
  		$this->tableName = $this->_lookupTableName($pObjType);
  	}
  	// Execute the query.
  	try {
  		// Begin a transaction.
  		$this->dbh->beginTransaction();
  		$lPdoSql = 'DELETE FROM ' . $this->tableName . ' WHERE source = "' . $pSource . '"';
  		if($this->isLogging && function_exists('krumo')) {
  		  krumo($lPdoSql);
  		}
  		// Only do the delete if this is not a dry run.
  		if(!$this->isDryRun) {
  	      $stmt = $this->dbh->prepare($lPdoSql);
  		  $stmt->execute();
  		  $lNumRows = $stmt->rowCount();
  		}
  		// End the transaction.
  		$this->dbh->commit();
  	}
  	// Catch an error if there was one.
  	catch(PDOException $e) {
  	  $this->dbh->rollBack();
  	  echo $e->getMessage();
  	}
  	return $lNumRows;
  } 
   
  /**
   * Delete records from the database that match certain values.
   */
  public function deleteRecords($pFieldName, array $pValues, $pType = self::TYPE_INT, $pObjType = self::RECORDS_JOB) {
  	$lNumRows = FALSE; // Assume error condition to start.
  	// Connect if no database handle.
  	if($this->dbh == NULL) {
  	  $this->connect();
  	}
  	// Set the table name from which to delete, if not already set.
  	// Default to the jobs table.
  	if(empty($this->tableName) || $pObjType != self::RECORDS_JOB) {
  	  $this->tableName = $this->_lookupTableName($pObjType);
  	}
  	// Only prepare statement if there are values.
  	if(count($pValues) == 0) {
  	  return $lNumRows;
  	}
  	// Execute the query.
  	try {
  	  // Begin a transaction.
  	  $this->dbh->beginTransaction();
  	  // Try to get a delete statement.
  	  try {
  	    $lPdoSql = $this->_buildDeleteStmt($this->tableName, $pFieldName, $pValues, $pType, self::OP_IN);
  	  }
  	  catch(Exception $e) {
  	  	if($this->isLogging) {
  	  	  echo $e->getMessage();
  	  	}
  	  	return $lNumRows;
  	  }
  	  // Get the bind value type.
  	  $lBindValueType = $this->_lookupBindValueType($pType);
  	  // Debug the statement if logging.
  	  if($this->isLogging && function_exists('krumo')) {
  	  	krumo(array('sql' => $lPdoSql, 'values' => $pValues));
  	  }
  	  // Only do the delete if this is not a dry run.
  	  if(!$this->isDryRun) {
  	    $stmt = $this->dbh->prepare($lPdoSql);
  	    $stmt->bindValue(':values', $pValues, $lBindValueType);
  	    $stmt->execute();
  	    $lNumRows = $stmt->rowCount();
  	  }
  	  // End the transaction.
  	  $this->dbh->commit();
  	}
  	// Catch an error if there was one.
  	catch(PDOException $e) {
  	  $this->dbh->rollBack();
  	  echo $e->getMessage();
  	}
  	return $lNumRows;
  }
  
  /**
   * Select all records.
   */
  public function selectAllRecords($pObjType = self::RECORDS_JOB, $pSelectFields = self::FIELDS_ALL, 
  		$pReturnAll = TRUE, $pFetchMode = PDO::FETCH_ASSOC) {
  	// Set return variables. Assume error condition to start.
  	$lRecords = FALSE;
  	$lNumRows = FALSE;
  	// Connect if no database handle.
  	if($this->dbh == NULL) {
  		$this->connect();
  	}
  	// Set the table name from which to delete, if not already set.
  	// Default to the jobs table.
  	if(empty($this->tableName) || $pObjType != self::RECORDS_JOB) {
  		$this->tableName = $this->_lookupTableName($pObjType);
  	}
  	// Execute the query.
  	try {
  		// Try to get a select statement.
  		try {
  			$lPdoSql = $this->_buildSelectAllStmt($this->tableName, $pSelectFields);
  		}
  		catch(Exception $e) {
  			if($this->isLogging) {
  				echo $e->getMessage();
  			}
  			return $lRecords;
  		}
  		// Debug the statement if logging.
  		if($this->isLogging && function_exists('krumo')) {
  			krumo(array('sql' => $lPdoSql, 'values' => $pValues));
  		}
  		// Selects can be done on dry runs.
  		$stmt = $this->dbh->prepare($lPdoSql);
  		$stmt->execute();
  		// If returning the full array, then build it here. Otherwise return the PDOStatement object.
  		if($pReturnAll == TRUE) {
  		  while($lRow = $stmt->fetch($pFetchMode)) {
  			$lRecords[] = $lRow;
  		  }
  		}
  		else {
  			$lRecords = $stmt;
  		}
  		$lNumRows = $stmt->rowCount();
  	}
  	// Catch an error if there was one.
  	catch(PDOException $e) {
  		echo $e->getMessage();
  	}
  	return $lRecords;
  }
  
  /**
   * Select records by a given set of criteria.
   * The records are returned as a single array, or as a PDOStatement if requested that way.
   */
  public function selectRecords($pFieldName, array $pValues, $pFieldType = self::TYPE_INT, $pSelectFields = self::FIELDS_CORE,
  		$pObjType = self::RECORDS_JOB, $pReturnAll = TRUE, $pFetchMode = PDO::FETCH_ASSOC) {
  	// Set return variables. Assume error condition to start.
  	$lRecords = FALSE;
  	$lNumRows = FALSE;
  	// Connect if no database handle.
  	if($this->dbh == NULL) {
  		$this->connect();
  	}
  	// Set the table name from which to delete, if not already set.
  	// Default to the jobs table.
  	if(empty($this->tableName) || $pObjType != self::RECORDS_JOB) {
  		$this->tableName = $this->_lookupTableName($pObjType);
  	}
  	// Only prepare statement if there are values.
  	if(count($pValues) == 0) {
  		return $lRecords;
  	}
  	// Execute the query.
  	try {
  	  // Try to get a select statement.
  	  try {
  		$lPdoSql = $this->_buildSelectStmt($this->tableName, $pFieldName, $pValues, $pFieldType, $pSelectFields, self::OP_IN);
  	  }
  	  catch(Exception $e) {
  	  	if($this->isLogging) {
  	  		echo $e->getMessage();
  	  	}
  	  	return $lRecords;
  	  }
  	  // Debug the statement if logging.
  	  if($this->isLogging && function_exists('krumo')) {
  	  	krumo(array('sql' => $lPdoSql, 'values' => $pValues));
  	  }
  	  // Selects can be done on dry runs.
  	  $stmt = $this->dbh->prepare($lPdoSql);
  	  $stmt->execute();
  	  // If returning the full array, then build it here. Otherwise return the PDOStatement object.
  	  if($pReturnAll == TRUE) {
  	  	while($lRow = $stmt->fetch($pFetchMode)) {
  	  	  $lRecords[] = $lRow;
  	  	}
  	  }
  	  else {
  	  	$lRecords = $stmt;
  	  }
  	  $lNumRows = $stmt->rowCount();
  	}
  	// Catch an error if there was one.
  	catch(PDOException $e) {
  	  echo $e->getMessage();
  	}
  	return $lRecords;
  }
  
  /**
   * Write to the database an array of records. 
   * By default, write to the jobs table.
   */
  public function createRecords($pRecords, $pType = self::RECORDS_JOB) {
    $lNumRows = FALSE; // Assume error condition to start.
    // Connect if no database handle.
    if($this->dbh == NULL) {
    	$this->connect();
    }
    // Set the table name to which to write based on type,
    // if not already set.
    if(empty($this->tableName)) {
      $this->tableName = $this->_lookupTableName($pType);
    }
    // Validate that the records can be written to this table.
    $errors = $this->_validateRecords($pRecords, $pType);
    if($errors == FALSE) {
      // Add GUIDs to the records.
      $lRecords = $this->_addRecordGuids($pRecords, $pType);
      // Actually create the records.
      $lNumRows = $this->_createRecords($lRecords, $pType);
    } 
    return $lNumRows;
  }

  /* Private function to do the dirty work of writing to the DB. */
  private function _createRecords($pRecords, $pType) {
  	$lNumRows = 0;
  	$lRecords = $pRecords;
  	// If the type should be de-duped, then do that before creating records.
  	// @todo: Check by type in a separate function.
  	if($pType == self::RECORDS_JOB) {
  	  $lRecords = $this->_dedupeRecords($lRecords, $pType);
  	}
  	// After de-duping, only do an insert if records still remain to be inserted.
  	if(count($lRecords) > 0) {
  	  try {
  	    // Begin a transaction.
  	    $this->dbh->beginTransaction();
  	    // Set up the PDO statement.
  	    // Note that for this to work, the first record in the set must have keys.
  	    $lRecord = current($lRecords);
        $lFields = array_keys($lRecord);
        $lPdoSql = $this->_buildInsertStmt($this->tableName, $lFields);
        // Iterate and insert the records.
        // @todo: Bind them instead.
        foreach($lRecords as $lRecord) {
          $lPdoValues = $this->_buildValues($lRecord);
          $stmt = $this->dbh->prepare($lPdoSql);
          // Debug the statement if logging.
          if($this->isLogging && function_exists('krumo')) {
            krumo(array('sql' => $lPdoSql, 'values' => $lPdoValues));
          }
          // Only do the insert if this is not a dry run. 
          if(!$this->isDryRun) {
            $stmt->execute($lPdoValues);
            $lResult = $stmt->rowCount();
            $lNumRows = $lNumRows + $lResult;
          }
        }
        // End the transaction.
        $this->dbh->commit();
  	  }
  	  // Catch an error if there was one.
  	  catch(PDOException $e) {
  	    $this->dbh->rollBack();
  	    echo $e->getMessage();
  	  }
  	}
  	return $lNumRows;
  }
  
  /* Private function to de-dupe records by GUID. */
  private function _dedupeRecords($pRecords, $pType = self::RECORDS_JOB) {
  	// The GUIDs of the records should be the keys of the array.
  	$lRecordGuids = array_keys($pRecords);
  	$retRecords = $pRecords;
  	// If there are GUIDs, then look up matches.
  	if(count($lRecordGuids) > 0) {
  	  $lMatchRecords = $this->selectRecords('guid', $lRecordGuids, self::TYPE_STRING, self::FIELDS_GUID, $pType);
  	}
  	// Unset the matching records from the array, so they will not be inserted.
  	if(is_array($lMatchRecords) && count($lMatchRecords > 0)) {
  	  foreach($lMatchRecords as $record) {
  	    $lGuid = $record['guid'];	
  	    unset($retRecords[$lGuid]);
  	  }
  	}
  	return $retRecords;
  }

  function buildSelectFields($pSelectFields) {
  	$lFields = '';
  	// Set the fields to select.
  	if($pSelectFields == self::FIELDS_ALL) {
  		$lFields = '*';
  	}
  	else if($pSelectFields == self::FIELDS_CORE) {
  		$lFields = 'id, source_guid, guid, title, org_name, referralurl, location_street, location_city, location_province, location_postal_code, location_country, latitude, longitude, created_date, changed_date, description';
  	}
  	else if($pSelectFields == self::FIELDS_GUID) {
  		$lFields = 'id, guid, title';
  	}
  	else if($pSelectFields == self::FIELDS_LOCATION) {
  		$lFields = 'id, title, location_street, location_city, location_province, location_postal_code, location_country, latitude, longitude';
  	}
  	else {
  		if(is_array($pSelectFields)) {
  			$lFields = implode(',', $pSelectFields);
  		}
  		else {
  			$lFields = $pFields;
  		}
  	}
  	return $lFields;
  }
  
  private function _buildSelectAllStmt($pTableName, $pSelectFields) {
  	$lPdoSql = '';
  	// Set the fields to select.
  	$lFields = $this->buildSelectFields($pSelectFields);
  	// Build the statement itself.
  	$lPdoSql = 'SELECT ' . $lFields . ' FROM ' . $pTableName;
  	return $lPdoSql;	
  }
  
  private function _buildSelectStmt($pTableName, $pFieldName, $pValue, $pFieldType, $pSelectFields, $pOperator) {
  	$lPdoSql = '';
  	// Set the fields to select.
  	$lFields = $this->buildSelectFields($pSelectFields);
  	if($pOperator == self::OP_IN) {
  		$lInClause = $this->_buildInClause($pValue, $pType);
  		$lPdoSql = 'SELECT ' . $lFields . ' FROM ' . $pTableName . ' WHERE ' . $pFieldName . ' IN ' . $lInClause;
  	}
  	else {
  		$lPdoSql = 'SELECT ' . $lFields . ' FROM ' . $pTableName . ' WHERE ' . $pFieldName . ' = :value';
  	}
  	return $lPdoSql;
  }
  
  private function _buildInsertStmt($pTableName, $pFields) {
  	$lFieldNamesStr = implode(',', $pFields);
  	$lNamedParams = array();
  	$lNamedParamsStr = '';
  	$lPdoSql = '';
  	foreach($pFields as $field) {
  		$lNamedParams[] = ':' . $field;
  	}
  	$lNamedParamsStr = implode(',', $lNamedParams);
  	$lPdoSql = 'INSERT INTO ' . $pTableName . '(' . $lFieldNamesStr . ') VALUES(' . $lNamedParamsStr . ')';
  	return $lPdoSql;
  }
  
  private function _buildDeleteStmt($pTableName, $pFieldName, $pValue, $pType = self::TYPE_INT, $pOperator = self::OP_IN) {
  	if($pOperator == self::OP_IN) {
  	  $lInClause = $this->_buildInClause($pValue, $pType);
  	  $lPdoSql = 'DELETE FROM ' . $pTableName . ' WHERE ' . $pFieldName . ' IN ' . $lInClause;
  	}
  	else {
  	  // @todo: Add in other options.
  	  $lPdoSql = 'DELETE FROM ' . $pTableName . ' WHERE ' . $pFieldName . ' = :value';
  	}
    return $lPdoSql;
  }
  
  private function _buildInClause($pValue, $pType) {
  	$lInClause = '';
  	if(!is_array($pValue)) {
  		$lValues = (array) $pValue;
  	}
  	else {
  		$lValues = $pValue;
  		$lValuesStr = '';
  		// Prepare the values for the IN clause.
  		if($pType == self::TYPE_INT) {
  			array_walk($lValues, 'val_toInt');
  		}
  		else if($pType == self::TYPE_STRING) {
  			array_walk($lValues, 'val_quote');
  		}
  		else {
  			throw new Exception('Unsupported type.');
  		}
  		$lValuesStr = implode(',', $lValues);
  	}
  	$lInClause = '(' . $lValuesStr . ')';
  	return $lInClause;
  }
  
  /* Looks up the proper PDO bind value type for this type of value. */
  private function _lookupBindValueType($pType = self::TYPE_INT) {
  	switch($pType) {
  		case self::TYPE_BOOL:
  			$lBindValueType = PDO::PARAM_BOOL;
  		case self::TYPE_STRING:
  			$lBindValueType = PDO::PARAM_STR;
  			break;
  		case self::TYPE_INT:
  		default:
  			$lBindValueType = PDO::PARAM_INT;
  	}
  	// @todo: Have an error condition if the type cannot be bound (i.e., arrays?)
  	return $lBindValueType;
  }
  
  private function _buildValues($pRecord) {
    $lPdoValues = array();
    foreach($pRecord as $fieldName => $value) {
      $value = utf8_decode($value); // If value is in UTF-8 decode it. @todo: Why is this necessary?
      $lPdoValues[':' . $fieldName] = $value;
    }
    return $lPdoValues;
  }
 
  /* Lookup the database table name by record type. */
  private function _lookupTableName($type = self::RECORDS_JOB) {
    $lTableName = '';
    // By default, use tbl_opportunities.
    switch($type) {
      case self::RECORDS_JOB:
      default:
        $lTableName = 'tbl_opportunities'; 
    }
    return $lTableName;
  }
     

  private function _validateRecords($records, $type = self::RECORDS_JOB) {
    $validation_errors = FALSE;
    // Check that there is a records array.
    if(!is_array($records)) {
     $validation_errors[] = self::RES_ERROR_NOT_ARRAY;
    }
    // Check that it has members.
    else if(count($records) == 0) {
     $validation_errors[] = self::RES_ERROR_NO_MEMBERS;
    }
    // Return early if either of these are the case.
    // Otherwise, you could be in a situation 
    // where you were trying to validate schema on an empty array. 
    if(is_array($validation_errors)) {
      return $validation_errors;
    }
    // Compare the record type to the table schema.
    $record_type_error = $this->_checkRecordType($type);
    if(!empty($record_type_error)) {
      $validation_errors[] = $record_type_error;
      // Compare the record values to the table schema.
      /* $schema_errors = _checkSchema($records);
      if(is_array($schema_errors) && count($schema_errors) > 0) {
        $validation_errors[] += $schema_errors;
      } */
    }
    return $validation_errors;
  }

  private function _checkRecordType($type = self::RECORDS_JOB) {
    $lTables = $this->getTables();
    $error = NULL;
    if(!array_key_exists($this->tableName, $lTables)) {
      $error = self::RES_ERROR_UNDEFINED_TABLE;
    }
    else if($lTables[$this->tableTable] != $type) {
      $error = self::RES_ERROR_WRONG_DATA;
    }
  }
  
  private function _checkSchema($records) {
  	// Get the schema for the table.
    $lTableSchema = $this->getSchema($this->tableName);
    // Use the first record as representative for checking schema.
    // Probably is not necessary at this stage to drop invalid records prior to insert, 
    // but just assume based on the first one that the array structure is consistent.
    $lRecord = $records[0];
    
    // @todo: Actually check the records against the schema.
    return void;
  }
  
  /* Only call this after records have been validated. */
  private function _addRecordGuids($pRecords, $type = self::RECORDS_JOB) {
  	$retRecords = array();
  	$lGuid = '';
  	$i = 0;
  	foreach($pRecords as $record) {
  	  // @todo: Make it possible to configure how GUID is generated.
  	  // Skip records with no title. They shouldn't be inserted.
  	  if((!isset($record['guid']) || empty($record['guid'])) && !empty($record['title'])) {
  	  	// Set the GUID to a number if it wasn't able to grab a GUID for a record.
  	  	// This is needed so the de-duping can work.
  	  	$lGuid = $i;
  	  	// All records should have a source and a source GUID.
  	  	// For now, it should be adequate to use source:source_guid as the GUID.
  	    if(!empty($record['source']) && !empty($record['source_guid'])) {
  	      $lGuid = $record['source'] . ':' . $record['source_guid'];
  	    }
  	    else {
  	      if($this->isLogging && function_exists('krumo')) {
  	    	krumo(array('missing source_guid for ' . $i => $record));
  	      }
  	    }
  	    // Key the returned records by guid so they can be deduped.
  	    $retRecords[$lGuid] = $record;
  	    $retRecords[$lGuid]['guid'] = $lGuid;
  	  }
  	  else {
  	  	if($this->isLogging && function_exists('krumo')) {
  	  	  krumo(array('skipped ' . $i => $record));
  	  	}
  	  }
  	  $i++;
  	}
  	return $retRecords;
  }
  
  /*
  public function getSchema($pTableName = '') {
   // Array of table schemas.
   // Concept borrowed from Drupal.
   $schema = array('tbl_opportunities' =>
                array('id' =>
                  array('type' => TYPE_INT,
                        'required' => FALSE,
                        'description' => 'Autoincrement')),
                array('title' =>
                  array('type' => TYPE_STRING,
                        'required' => TRUE,
                        'description' => 'Title of job')),
                array('changed' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => TRUE,
                         'description' => 'Date job changed')),
                array('teaser' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Short version of description')),
                array('description' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Job description.')),
                array('requirements' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Job requirements.')),
                array('org_name' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Organization name.')),
                array('start_date' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => FALSE,
                         'description' => 'Start date of job.')),
                array('end_date' =>
                   array('type' => TYPE_UNIXTIME,
                         'required' => FALSE,
                         'description' => 'End date of job.')),
                array('source' =>
                   array('type' => TYPE_STRING,
                         'required' => TRUE,
                         'description' => 'Source of feed data.')),
                array('url_alias' =>
                   array('type' => TYPE_STRING,
                         'required' => FALSE,
                         'description' => 'Short URL alias.')),
                array('full_url_alias' =>
                    array('type' => TYPE_STRING,
                          'required' => FALSE,
                          'description' => 'Full URL alias.')),
    );
    if(empty($lTableName)) {
      return $schema;
    }
    else {
      if(array_key_exists($lTableName, $schema)) {
        return $schema[$lTableName];
      }
    }
  } */ 

  /* Checks the requirements for the class. */
  private function _checkRequirements() {
  	// PDO class is required.
  	if(!class_exists('PDO')) {
  	  throw new Exception('PDO class not available.');
  	}
  	
  	// The pdo_ext.class.php file is required.
  	if(file_exists('..' . DIRECTORY_SEPARATOR . 'pdo_ext.class.php')) {
  	  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pdo_ext.class.php');
  	}
  	else {
  	  throw new Exception('PDO_Ext class file not present.');
  	}
  	
  	// The class must have been loaded from that file.
  	if(!class_exists('PDO_Ext')) {
  	  throw new Exception('PDO_Ext class not available.');
  	}
  }
  
  private function _setCfgFile($pCfgFile) {
  	$default = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'dbconfig.ini';
  	if(empty($pCfgFile)) {
  	  $this->cfgFile = $default;
  	}
  	else {
  	  // Only read .ini files that exist.
  	  if(substr($pCfgFile, -4) == '.ini' && file_exists($pCfgFile)) {
  		$this->cfgFile = $pCfgFile;
  	  }
  	  else {
  		$this->cfgFile = $default;
  	  }
  	}
  }
  
  /* Sets the database information based on the config file. */
  private function _setDBInfo() {
    // Check that the ini file exists.
    if(!file_exists($this->cfgFile)) {
      throw new Exception('Ini file does not exist.');
    }

    // Check if the ini file was parseable.
    $lDbInfo = parse_ini_file($this->cfgFile);
    // Check if the ini file was parseable.
    if(empty($lDbInfo) || !is_array($lDbInfo)) {
      throw new Exception('Ini file not parseable.');
    }
    // Check if the ini file contained the proper values.
    if(!empty($lDbInfo['hostname']) && !empty($lDbInfo['db_name'])
      && !empty($lDbInfo['username'])
      && !empty($lDbInfo['password'])) {
      $this->dbInfo = $lDbInfo;
    }
    else {
      $err_result = var_dump($lDbInfo);
      throw new Exception('There were missing required properties for database connection in ' . $this->cfgFile . ' Parsed value was: ' . $err_result);
    }
  }

  /* Sets the connection string for connecting to the database. */
  private function _setConnStr() {
    $this->connStr = 'mysql:host=' . $this->dbInfo['hostname'] . ';dbname=' . $this->dbInfo['db_name'];
  }

  private function _echoOrReturn($var, $echo = FALSE) {
    if($echo) {
      echo $var;
    }
    else {
      return $var;
    }
  }
}
