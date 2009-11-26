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



class Flex_Mssql_Driver extends Database_Mssql_Driver{
	
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
				$this->query_cache[$hash] = new Flex_Mssql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flex_Mssql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	
}



class Flex_Mysqli_Driver extends Database_Mysqli_Driver{
	
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
				$this->query_cache[$hash] = new Flex_Mysqli_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flex_Mysqli_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	
}



class Flex_Pdosqlite_Driver extends Database_Pdosqlite_Driver{
	
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
				$this->query_cache[$hash] = new Flex_Pdosqlite_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flex_Pdosqlite_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	
}



class Flex_Pgsql_Driver extends Database_Pgsql_Driver{
	
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
				$this->query_cache[$hash] = new Flex_Pgsql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		return new Flex_Pgsql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}	
	
}

