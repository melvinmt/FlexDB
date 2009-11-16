<?php

class Flexdriver extends Database_Mysql_Driver{
	
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
				$this->query_cache[$hash] = new Flexresult(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flexresult(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	 
}