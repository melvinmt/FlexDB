<?php

class Flexresult extends Mysql_Result{
	
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
		
		if ($num_fields > 0)
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