<?php

class Flex_Mysql_Driver extends Database_Mysql_Driver{
	
	public function __construct($config){
		
		parent::__construct($config);
		
	}
	
	public function query($sql)
	{
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET|DELETE|TRUNCATE)\b#i', $sql))
		{
			$hash = $this->query_hash($sql);

			if ( ! isset($this->query_cache[$hash]))
			{
				// Set the cached object
				$this->query_cache[$hash] = new Flex_Mysql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flex_Mysql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	
}
class Flex_Mysql_Result extends Mysql_Result{
	
	public function __construct($result, $link, $object = TRUE, $sql){
		parent::__construct($result, $link, $object = TRUE, $sql);	
	}
	
	public function as_array(){
		
		$array = $this->real_array();
		
		if(count($array) > 0){
			
			return $array;
			
		}else{
			
			return false;
		}
	}

	
	public function single_row(){
				
		$array = $this->real_array();
		
		if(isset($array[0])){
			
			return $array[0];
			
		}else{
			
			return false;
		}
	}
	
	public function single_value(){
		
		$array = $this->real_array(false);
		
		if(isset($array[0]) && is_array($array[0]) && count($array[0]) > 0){
			
			return reset($array[0]); 
			
		}else{
			
			return false;
		}
		
	}
	
	public function real_array(){
		
		$num_fields = mysql_num_fields($this->result);
		
		$rows = $this->result_array(false);
		
		if ($num_fields > 0 && count($rows) > 0)
		{
						
			for ($i=0; $i < $num_fields; $i++) {
				
				$fields[mysql_field_name($this->result, $i)] = array(mysql_field_type($this->result, $i), mysql_field_len($this->result, $i));							
			}
			
			foreach($rows as &$array){
				
				foreach ($array as $field_name => &$value){
					
					switch($fields[$field_name][0]){
						
						case "int":
							
							if($fields[$field_name][1] === 1){
								
								if($value == 1){
									
									$value = true;
									
								}else{
									
									$value = false;
								}	
							}else{
						
								$value = intval($value);
							}
							
						break;
						
						case "real":
						
							$value = floatval($value);
							
						break;
						
					}
					
				}
				
			}	
			
			return $rows;	
		}
	}
	
	public function success(){

		if($this->count() > 0){
			
			return true;
			
		}else{
			
			return false;
		}
		
	}
	

	
}