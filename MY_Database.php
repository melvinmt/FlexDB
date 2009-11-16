<?php

class Database extends Database_Core {
	
	public $result;
	
	public function __construct(){
		
		parent::__construct();
		
		$this->driver = new Flexdriver($this->config);
		
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