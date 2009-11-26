<?php

class Database extends Database_Core {
	
	public $result;
	
	public function __construct($config = array()){
		
		parent::__construct();
		
		if (empty($config))
		{
			// Load the default group
			$config = Kohana::config('database.default');
		}
		elseif (is_array($config) AND count($config) > 0)
		{
			if ( ! array_key_exists('connection', $config))
			{
				$config = array('connection' => $config);
			}
		}
		elseif (is_string($config))
		{
			// The config is a DSN string
			if (strpos($config, '://') !== FALSE)
			{
				$config = array('connection' => $config);
			}
			// The config is a group name
			else
			{
				$name = $config;
		
				// Test the config group name
				if (($config = Kohana::config('database.'.$config)) === NULL)
					throw new Kohana_Database_Exception('database.undefined_group', $name);
			}
		}
		
		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);
		
		if (is_string($this->config['connection']))
		{
			// Make sure the connection is valid
			if (strpos($this->config['connection'], '://') === FALSE)
				throw new Kohana_Database_Exception('database.invalid_dsn', $this->config['connection']);
		
			// Parse the DSN, creating an array to hold the connection parameters
			$db = array
			(
				'type'     => FALSE,
				'user'     => FALSE,
				'pass'     => FALSE,
				'host'     => FALSE,
				'port'     => FALSE,
				'socket'   => FALSE,
				'database' => FALSE
			);
		
			// Get the protocol and arguments
			list ($db['type'], $connection) = explode('://', $this->config['connection'], 2);
		
			if (strpos($connection, '@') !== FALSE)
			{
				// Get the username and password
				list ($db['pass'], $connection) = explode('@', $connection, 2);
				// Check if a password is supplied
				$logindata = explode(':', $db['pass'], 2);
				$db['pass'] = (count($logindata) > 1) ? $logindata[1] : '';
				$db['user'] = $logindata[0];
		
				// Prepare for finding the database
				$connection = explode('/', $connection);
		
				// Find the database name
				$db['database'] = array_pop($connection);
		
				// Reset connection string
				$connection = implode('/', $connection);
		
				// Find the socket
				if (preg_match('/^unix\([^)]++\)/', $connection))
				{
					// This one is a little hairy: we explode based on the end of
					// the socket, removing the 'unix(' from the connection string
					list ($db['socket'], $connection) = explode(')', substr($connection, 5), 2);
				}
				elseif (strpos($connection, ':') !== FALSE)
				{
					// Fetch the host and port name
					list ($db['host'], $db['port']) = explode(':', $connection, 2);
				}
				else
				{
					$db['host'] = $connection;
				}
			}
			else
			{
				// File connection
				$connection = explode('/', $connection);
		
				// Find database file name
				$db['database'] = array_pop($connection);
		
				// Find database directory name
				$db['socket'] = implode('/', $connection).'/';
			}
		
			// Reset the connection array to the database config
			$this->config['connection'] = $db;
		}
		// Set driver name
		$driver = 'Flex_'.ucfirst($this->config['connection']['type']).'_Driver';
		
		// Load the driver		// 
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Database_Exception('core.driver_not_found', $this->config['connection']['type'], get_class($this));
		
		// Initialize the driver
		$this->driver = new $driver($this->config);
		
		// Validate the driver
		if ( ! ($this->driver instanceof Database_Driver))
			throw new Kohana_Database_Exception('core.driver_implements', $this->config['connection']['type'], get_class($this), 'Database_Driver');
		
		Kohana::log('debug', 'Database Library initialized');
		
	}
	
	public function get($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table != ''){
			
			$this->from($table);
		}

		if ( ! is_null($limit)){
			
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select(get_object_vars($this));

		$this->reset_select();

		$this->result = $this->query($sql);

		$this->last_query = $sql;
		
		return $this->result;
	}
	
	public function getwhere($table = '', $where = NULL, $limit = NULL, $offset = NULL)
	{
		if ($table != ''){
			$this->from($table);
		}

		if ( ! is_null($where)){
			$this->where($where);
		}

		if ( ! is_null($limit)){
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select(get_object_vars($this));

		$this->reset_select();

		$this->result = $this->query($sql);

		return $this->result;
	}
	
	public function query($sql = '')
	{
		if ($sql == '') return FALSE;

		// No link? Connect!
		$this->link or $this->connect();

		// Start the benchmark
		$start = microtime(TRUE);

		if (func_num_args() > 1){
			$argv = func_get_args();
			$binds = (is_array(next($argv))) ? current($argv) : array_slice($argv, 1);
		}

		// Compile binds if needed
		if (isset($binds)){
			$sql = $this->compile_binds($sql, $binds);
		}

		// Fetch the result
		$this->result = $this->driver->query($this->last_query = $sql);

		// Stop the benchmark
		$stop = microtime(TRUE);

		if ($this->config['benchmark'] == TRUE){
			// Benchmark the query
			Database::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($this->result));
		}

		return $this->result;
	}

	
	

}