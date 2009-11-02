<?php
/**
 * FlexDB Kohana Library 
 * A flexible database library which enables you to insert and/or update data in tables and fields
 * that don't exist yet. You can also use this class to dump data arrays into the database without
 * considering which fields do or do not exist (which is not possible with the standard Kohana 
 * insert/update method). 
 * 
 * This class uses the Kohana Database Query Builder: http://docs.kohanaphp.com/libraries/database/builder
 *
 * Although it is possible to call all the methods in this class when initialized, it is recommended
 * to only use the insert() and update() methods when inserting and/or updating data
 *
 * @author Melvin Tercan <mt@mediamedics.nl>
 * @copyright MediaMedics V.o.F. 
 * @link http://www.mediamedics.nl
 * @version 0.0.5
**/

class FlexDB{
	
	public static $db;
	public static $insert_id;
	
	/**
	 * Checks if there's an connection available, if false, connection will be made
	 * 
	 * @param string $connect // specify an database other than the default	
	**/
	public static function instance($connect = NULL){
		
		if($connect !== NULL){
			FlexDB::connect($connect);			
		}elseif(FlexDB::$db === NULL){
			FlexDB::connect();	
		}	
		
	}
	
	/**
	 * Connects to a database
	 * 
	 * @param string $database // uses the database config if specified
	**/
	public static function connect($database = NULL){
		if($database === NULL){
			FlexDB::$db = Database::instance();
		}else{
			FlexDB::$db = Database::instance($database);
		}
	}
	
	
	/**
	 * Inserts a new row in the database
	 * -> if the table doesn't exist yet, it will be created..
	 * -> if (certain) fields don't exist yet, they will be created..
	 *
	 * @param string $table_name // database table name
	 * @param array $data // array('field1' => 'value', ...)
	 * @param boolean $restrict // set to true to enable strict database inserts, the fields from your data array will be filtered against the existing fields in the database
	 * @param string $connect // specify an database other than the default												
	 * @return boolean // true on success
	**/
	public static function insert($table_name, $data, $restrict = false, $connect = NULL){
		
		FlexDB::instance($connect);
		
		if($restrict === false){
		
			if( FlexDB::exists($table_name) === true){
				
				$fields = FlexDB::fields($table_name);
				
				$diff = array_diff_key($data, $fields);
				
				if(count($diff) > 0){
					
					FlexDB::alter($table_name, $diff);
					
				}
				
				$data = FlexDB::json($data);
				
				if( ($query = FlexDB::$db->insert($table_name, $data)) ){
					
					FlexDB::$insert_id = $query->insert_id();
					
					return true;
				
				}
				
			}else{
				
				FlexDB::create($table_name, $data);
				
				$data = FlexDB::json($data);
				
				if( ($query = FlexDB::$db->insert($table_name, $data)) ) {
					
					FlexDB::$insert_id = $query->insert_id();
					
					return true;
					
				}
				
			}
			
		}else{
						
			$data = FlexDB::json($data);			
								
			$insert_data = FlexDB::match($table_name, $data);
								
			if( ($query = FlexDB::$db->insert($table_name, $insert_data)) ){
				
				FlexDB::$insert_id = $query->insert_id();
				
				return true;
				
			}
		}	
			
	}
	
	/**
	 * Updates a row in the database
	 * -> if (certain) fields don't exist yet, they will be created..
	 *
	 * @param string $table_name // database table name
	 * @param array $data // array('field1' => 'value', ...)
	 * @param array $where // array with where clausules e.g. array('id' => 4)
	 * @param boolean $restrict // set to true to enable strict database updates, the fields from your data array will be filtered against the existing fields in the database
	 * @param string $connect // specify an database other than the default												
	 * @return boolean // true on success
	**/	
	public static function update($table_name, $data, $where, $restrict = false, $connect = NULL){
				
		FlexDB::instance($connect);
		
		if($restrict === false){

			if( FlexDB::exists($table_name) === true){

				$fields = FlexDB::fields($table_name);

				$diff = array_diff_key($data, $fields);

				if(count($diff) > 0){

					FlexDB::alter($table_name, $diff);

				}
				
				$data = FlexDB::json($data);

				if( FlexDB::$db->update($table_name, $data, $where) ){

					return true;

				}

			}

		}else{

			$data = FlexDB::json($data);
			
			$update_data = FlexDB::match($table_name, $data);

			if( FlexDB::$db->update($table_name, $update_data, $where) ){

				return true;

			}
		}	
			
	}
	

	/**
	 * Models a new database table exactly how you want it.. 
	 *
	 * @param string $table_name // name of the new table 
	 * @param array $fields // nested array with following keys: 
	 *												- 'field' => (string) field name (required!)
	 *												- 'type' => (string) MySQL data type (e.g. INT, VARCHAR etc.)
	 *												- 'length' => (int) field length
	 * 												- 'attribute' => (string) attributes of field (e.g. signed, unsigned etc.)
	 *												- 'default' => (mixed) default value of field
	 *												- 'null' => (bool) true if you want default value to be NULL
	 *												- 'extra' => (string) extra attributes of string (e.g. auto_increment etc.)
	 * @param string $engine // MySQL storage engine, defaults to: MyIsam
	 * @param string $charset // table collation
	 * @param string $connect // specify an database other than the default												
	 * @return boolean // true on success								
	**/
	public static function model($table_name, $fields, $engine = 'MyISAM', $charset ='utf8', $connect = NULL){
		
		FlexDB::instance($connect);
		
		$tbl = "CREATE TABLE IF NOT EXISTS `{$table_name}`";
		$tbl .= "(";
		$tbl .= "`id` int(11) unsigned NOT NULL auto_increment, ";
		
		foreach ($fields as $field ){

			if( isset($field['field']) && $field['field'] != 'id' ){

				$tbl .= "`".$field['field']."` ";

				if(isset($field['type'])){
	
					$tbl .= $field['type']." ";
			
					if(isset($field['length'])){
						$tbl .= "(".$field['length'].") ";
					}
					
					if(isset($field['attribute'])){
						$tbl .= $field['attribute']." ";
					}
					
					if(isset($field['default'])){
						
						$tbl .= "default ".$field['default']." ";
						
					}
					
					if(isset($field['null']) && !isset($field['default'])){
						
						if($field['null'] === false){
							
							$tbl .= "default NOT NULL ";
							
						}else{
							
							$tbl .= "default NULL ";
							
						}
					}
					
					if(isset($field['extra'])){
						$tbl .= $field['extra']." ";
					}
			
			
				}
				
				$tbl .= ", ";
			}
		}
		
		$tbl .= "PRIMARY KEY (`id`)";		
		$tbl .= ") ";
		
		if(isset($engine)){
			$tbl .= "ENGINE=".$engine." ";
		}
		
		if(isset($charset)){
			$tbl .= "DEFAULT CHARSET=".$charset." ";
		}
		
		if( FlexDB::$db->query($tbl) ){
			
			return true;
			
		}
	}
	
	
	/**
	 * Returns array with fields and corresponding data types of the table
	 *
	 * @param mixed $table_name // database table name OR specify field_name with array('table_name' => 'field_name')
	 * @param string $connect // specify an database other than the default												
	 * @return array $fields 
	**/
	public static function show($table_name, $connect = NULL){
		
		FlexDB::instance($connect);
		
		if(is_array($table_name)){
			foreach ($table_name as $k=>$v){
				$table_name = $k;
				$field_name = $v;
			}
		}
			
		$q = "SHOW COLUMNS FROM `".$table_name."` ";
		
		if(isset($field_name)){
			$q .= "WHERE `Field` = '".$field_name."' ";	
		}
			
		
		$fields = FlexDB::$db->query($q)->result_array(false);
		
		return $fields;
		
	}
	
	/**
	 * Deletes a field from the table
	 *
 	 * @param string $field_name // field name you want to delete
	 * @param string $table_name // database table name
	 * @param string $connect // specify an database other than the default												
	 * @return boolean // true on success
	**/
	public static function del($field_name, $table_name, $connect = NULL){
	
		FlexDB::instance($connect);
	
		if( FlexDB::$db->query("ALTER TABLE `{$table_name}` DROP `{$field_name}`") ){
		
			return true;
		
		}
		
	}
	
	/**
	 * Drops a table
	 *
	 * @param string $table_name // database table name
	 * @param string $connect // specify an database other than the default	
	 * @return boolean // true on success
	**/
	public static function drop($table_name, $connect = NULL){
	
		FlexDB::instance($connect);
	
		if( FlexDB::$db->query("DROP TABLE `{$table_name}`") ){
			
			return true;
			
		}
		
	}
	
	/**
	 * Creates new fields in an existing table
	 *
	 * @param string $table_name // database table name
	 * @param array $array // array('field1' => 'doesnt matter', ...)
	 * @param string $connect // specify an database other than the default	
	 * @return boolean // true on success
	**/
	public static function alter($table_name, $array, $connect = NULL){
	
		FlexDB::instance($connect);
	
		foreach ($array as $key => $value){
			
			$datatype = FlexDB::setsqltype($value);
			FlexDB::$db->query("ALTER TABLE `{$table_name}` ADD `{$key}` {$datatype}");
			
		}
		
		return true;
		
	}
	
	/**
	 * Creates a new table
	 *
	 * @param string $table_name // database table name
	 * @param array $data // array('field1' => 'value', ...)
	 * @param string $charset // charset of the table, default: utf8
	 * @param string $storage // storage engine of the table, default: InnoDB
	 * @param string $connect // specify an database other than the default	
	 * @return boolean // true on success
	**/
	public static function create($table_name, $data, $charset = 'utf8', $storage = 'InnoDB', $connect = NULL){
		
		FlexDB::instance($connect);
		
		foreach ($data as $name => $value){
			
			$field[$name] = FlexDB::setsqltype($value);
			
		}
		
		$tbl = "CREATE TABLE IF NOT EXISTS `{$table_name}`";
		$tbl .= "(";
		$tbl .= "`id` int(11) unsigned NOT NULL auto_increment,";
		
			foreach ($field as $name => $type){
				
				$tbl .= "`{$name}` {$type},";
				
			}
		
		$tbl .= "PRIMARY KEY  (`id`)";
		$tbl .= ")";
		$tbl .= "ENGINE={$storage} ";  
		$tbl .= "DEFAULT CHARSET={$charset}";
		
		if( FlexDB::$db->query($tbl) ){
			
			return true;
			
		}
		
	}
	
	/**
	 * Checks if a table exists
	 *
	 * @param string $table_name // database table name
     * @param string $connect // specify an database other than the default	
	 * @return boolean // true if table exists
	**/
	public static function exists($table_name, $connect = NULL){
		
		FlexDB::instance($connect);
		
		$query = FlexDB::$db->query("SHOW TABLES LIKE '{$table_name}'")->result_array(false);
		
		if( isset($query[0])){
		
			return true;
		}
		
	}
	
	/**
	 * Sets SQL data type for field value
	 *
	 * @param string $value // value that needs to be checked
	 * @return string // SQL datatype string
	**/
	public static function setsqltype($value, $null = true){
				
		if($null === true){
			
			$null = "NULL";
			
		}else{
			
			$null = "NOT NULL";
			
		}
		
		$type = gettype($value);
		
		if(is_numeric($value)){
			
			if( (float)$value != (int)$value ){
				
	            return "FLOAT(11) {$null}";
	
	        }else{
		
	            return "INT(11) {$null}";
	
	        }
		
		}elseif(is_bool($value)){
	
			return "BOOL {$null}";
				
		}elseif(is_array($value) || is_object($value) ){

			return "TEXT {$null}";

		}elseif(is_string($value)){

			if(strlen($value) <= 255){

				return "VARCHAR(255) {$null}";

			}else{

				return "TEXT {$null}";

			}
		
		}
		
	}
	
	
	/**
	 * Returns assoc array with only field names of table
	 *
	 * @param string $table_name // database table name
     * @param string $connect // specify an database other than the default	
	 * @return array $array // associative array with field names as key 
	**/	
	public static function fields($table_name, $connect = NULL){
		
		FlexDB::instance($connect);
		
		$query = FlexDB::$db->query("SHOW COLUMNS FROM `{$table_name}`")->result_array(false);
		
		foreach ($query as $k => $v){

			$array[$v['Field']] = $v['Field'];

		}
		
		return $array;
		
	}
	
	/**
	 * Filters a data array to match only existing table fields and remove those values who don't match
	 *
	 * @param string $table_name // database table name
	 * @param array $data // array('field1' => 'value', ...)
	 * @return array $array // filtered array with only existing field values
	**/
	public static function match($table_name, $data){

		$fields = FlexDB::fields($table_name);
		
		foreach ($fields as $key => $value){
			if(isset($data[$key])){
				$array[$key] = $data[$key];
			}
		}
		if(isset($array)){
			return $array;
		}
	}
	
	/**
	 * Starts a new database transaction
	 *
	 * @param string $connect // specify an database other than the default	
	 * @return bool // true on success
	**/
	public static function start($connect = NULL){
		
		FlexDB::instance($connect);
		
		FlexDB::$db->query('START TRANSACTION');
		
		return true;
		
	}
	
	/**
	 * Commits the current database transaction
	 * @return bool // true on success
	**/	
	public static function commit(){
		
		if(FlexDB::$db === NULL){
			return false;
		}else{
			FlexDB::$db->query('COMMIT');
			return true;	
		}
		
	}

	/**
	 * Rollbacks the current database transaction
	 * @return bool // true on success
	**/	
	public static function rollback(){
		
		if(FlexDB::$db === NULL){
			return false;
		}else{
			FlexDB::$db->query('ROLLBACK');
			return true;	
		}
		
	}
	
	/**
	 * Converts nested arrays to json encoded strings
	 *
	 * @param array $data // array('field1' => 'value', ...)
	 * @return array $data // array with json encoded nested arrays 
	**/
	public static function json($data){
				
		foreach ($data as $k => &$v){
			
			if(is_array($v)){
				
				$v = json_encode($v);
				
			}
			
		}
		
		return $data;
		
	}

	
}