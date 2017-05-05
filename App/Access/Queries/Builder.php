<?php
namespace App\Access\Queries;

use \PDO;
use App\Core\Setup;
use App\Security\Check;

class Builder
{
	/**
	 * @var string generated with constructor
	 */
	private $table;
	/**
	 * All var used to create request
	 */
	private $req_type;
	private $first_args = '';
	private $args;
	private $sql;
	private $stmt;
	private $tags = array();
	private $order = false;
	private $limit = false;
	private $where = false;
	private $offset = false;
	private $fetchable = true;
	private $fetchAll = true;
	private $join = false;
	private $join_type;
	private $join_table;
	private $on;
	private $orOn = false;

	/**
	 * Set table where the query will be on
	 * @param string $table 
	 */
	public function __construct($table)
	{
		$this->table = $table;
	}

	/**
	 * @return object fetchAll
	 */
	private function getAll()
	{
		$stmt = $this->exec();
		if ($stmt->errorCode() === '00000') {

        	return $stmt->fetchAll(PDO::FETCH_OBJ); 		
    	}
		die($stmt->errorInfo()[2]);
	}

	/**
	 * @return object fetch
	 */
	public function get()
	{
		if ($this->fetchAll) {

			return $this->getAll();
		}
		$stmt = $this->exec();
		if ($this->fetchable) {
			$this->resetQuery();
			if ($stmt->errorCode() == '00000') {

		       	return $stmt->fetch(PDO::FETCH_OBJ); 		
		   	}
			die($stmt->errorInfo()[2]);
		}
		$this->resetQuery();
		if ($stmt->rowCount()) {

			return "It wurkt bruuuuh !!!";
		} else {

			return "That code is a biiiitch";
		}
	}

	/**
	 * Set and execute sql request
	 * @return PDO statement
	 */
	private function exec()
	{
		$this->sql = $this->setSql();
		$pdo = Setup::getConnexion();
		$stmt = $pdo->prepare($this->sql);
		$stmt->execute($this->tags);

		return $stmt;
	}

	/**
	 * select column(s) 
	 * @param 	string  column(s)
	 * @return 	object 	$this
	 */
	public function select()
	{	
		if (!empty(func_get_args())) {
			$this->req_type = "SELECT";
			if (!empty($this->first_args)) {
				$this->first_args = sprintf('%s, ', $this->first_args);
			}
			$this->first_args = sprintf('%s%s', $this->first_args, $this->setArgs(func_get_args()));

			return $this;
		} else {
			die('select something duuuuuude !!!');
		}
	}

	/**
	 * @param  int 		$int 	id de la ligne à delete
	 * @return instance
	 */
	public function delete($int = 0)
	{
		if ($int && is_numeric( $int )) {
			$this->fetchAll = false;
			$this->fetchable = false;
			$this->req_type = "DELETE";
			$this->where($int);

			return $this;
		}
		die('FUUUUU BRUH !!!');
	}

	/**
	 * @param  array 	$datas 
	 * @return instance $this
	 */
	public function update($datas)
	{
		if (!empty( $datas )) {
			$this->fetchable = false;
			$this->fetchAll = false;
			$this->req_type = "UPDATE";
			$datas = Check::checkArgs($datas);
			$this->tags = $this->setTags($datas);
			if (!empty($this->first_args)) {
				$this->first_args = sprintf('%s, ', $this->first_args);
			}
			$this->first_args = sprintf('', $this->first_args, $this->setArgs($datas));
	
			return $this;
		} else {
			die('Tell me wut 2 update bruh... I aint even kiddin');
		}
	}

	/**
	 * @param  array 	$datas 	[$column => $value]
	 * @return instance $this
	 */
	public function insert($datas)
	{
		if (!empty( $datas )) {
			$this->req_type = "INSERT INTO";
			$this->fetchable = false;
			$this->fetchAll = false;
			$this->tags = $this->setTags($datas);
			$this->first_args = $this->setArgs($datas);
		}

		return $this;
	}

	/**
	 * @param 	string 	 		column selected	
	 * @return 	instance $this
	 */
	public function getFirst()
	{
		if (!empty(func_get_args())) {
			foreach (func_get_args() as $key => $value) {
				$this->select($value);
			}
		} else {
			$this->select('*');
		}
			$this->order('id', 'ASC');
			$this->limit(1);

			return $this;
	}

	/**
	 * @param 	string 	 		column selected	
	 * @return 	instance $this
	 */
	public function getLast()
	{
		if (!empty(func_get_args())) {
			foreach (func_get_args() as $key => $value) {
				$this->select($value);
			}
		} else {
			$this->select('*');
		}
			$this->order('id', 'DESC');
			$this->limit(1);

			return $this;
		

		return "select smth plzzzz bruuuuuuuh !!!!!!!";
	}

	/**
	 * @param  string 		$table 		name of the table joined
	 * @param  string 		$type  		join type (default = LEFT JOIN)
	 * @return instance 	$this
	 */
	public function join($table, $type = "left")
	{
		$this->join[] = strtoupper($type);
		$this->join_table[] = $table;

		return $this;
	}

	/**
	 * Add "ON" on the sql request with JOIN
	 * @param  string 		$col_ref     
	 * @param  string 		$col_compared
	 * @param  string 		$operator    
	 * @return instance 	$this
	 */
	public function on($col_ref, $col_compared, $operator = "=")
	{
		if (!empty($this->join) && (count($this->on) === count($this->join) - 1)) {
			if (count(explode('.', $col_ref)) !== 1 && count(explode('.', $col_compared)) !== 1) {
				$this->on[] = sprintf('%s %s %s', $col_ref, $operator, $col_compared);
			} else {
				die('Tell me on wut table your cols are brruuuuuuuh !!!!!');

				return;
			}
			
			return $this;
		}
		die('Join first brruuuuuuuh !!!!!');

		return;
	}

	/**
	 * @param 	string 		$column
	 * @param 	string 		$order 		type of order
	 * @return 	instance 	$this
	 */
	public function order($column, $order)
	{
		$this->order = sprintf('%s %s', $column, $order);

		return $this;
	}

	/**
	 * Add a where to sql query
	 * @param  mixed 		$value	
	 * @param  string 		$col 	 	id if not specified
	 * @param  string 		$operator 	comparaison tool, set as "=" if none
	 * @return instance
	 */
	public function where($value, $col = 'id', $operator = '=')
	{
		if ($col === 'id') {
			$this->fetchAll = false;
		}
		$this->where = sprintf('%s%s:%s', $col, $operator, $col);
		$this->tags[sprintf(":%s", $col)] = $value;

		return $this;
	}

	/**
	 * @return int count(*)
	 */
	public function count()
	{
		$this->req_type = "SELECT";
		$this->first_args = "COUNT(*)";
		$this->fetchAll = false;

		return $this;
	}

	/**
	 * Set limit
	 * @param  string 	$limit 
	 * @return instance
	 */
	public function limit($limit)
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @param  string   $offset
	 * @return instance
	 */
	public function offset($offset)
	{
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Reset $this->param
	 * @return void
	 */
	private function resetQuery()
	{
		unset($this->req_type);
		$this->first_args = '';
		unset($this->args);
		unset($this->sql);
		unset($this->stmt);
		$this->tags = array();
		$this->order = false;
		$this->limit = false;
		$this->where = false;
		$this->fetchAll = true;
		$this->fetchable = true;
		$this->offset = false;
		$this->join = false;
		unset($this->join_table);

		return;
	}

	/**
	 * Make nice sql syntaxe to get ready to execute
     * @return string   $sql
	 */
	private function setSql()
	{
		if ($this->req_type === "UPDATE") {
			$sql = sprintf('%s %s SET', $this->req_type, $this->table);
			$sql = sprintf('%s %s', $sql, $this->first_args);
		} elseif ($this->req_type === "INSERT INTO") {
			$tags = $this->setArgs($this->tags);
			$sql = sprintf('%s %s (%s) VALUES (%s)', $this->req_type, $this->table, $this->first_args, $tags);
		} else {
			$sql = sprintf('%s %s FROM %s', $this->req_type, $this->first_args, $this->table);
		}

		if ($this->join) {
			$length = count($this->on);
			if (count($this->join_table) === $length) {
				for ($i = 0; $i < $length; $i++) {
					$sql = sprintf('%s %s JOIN %s ON %s', $sql, $this->join[$i], $this->join_table[$i], $this->on[$i]);
				}
			} else {
				die('You must have as many ON as joins duuuuuuuuuuuuuude');
			}
		}
		if ($this->order) {
			$sql = sprintf('%s ORDER BY %s', $sql, $this->order);
		}
		if ($this->where) {
			$sql = sprintf('%s WHERE %s', $sql, $this->where);
		}
		if ($this->limit) {
			$sql = sprintf('%s LIMIT %s', $sql, $this->limit);
		}
		if ($this->offset) {
			$sql = sprintf('%s OFFSET %s', $sql, $this->offset);
		}

		return $sql;
	}

	/**
	 * @param string 	$args 	first args line
	 */
	private function setArgs($args)
	{
		$args = Check::checkArgs($args);
		$first_args = '';
		if ($this->req_type === "SELECT") {
			foreach ($args as $key => $value) {
				$first_args = sprintf('%s %s.%s,', $first_args, $this->table ,$value);
			}
		} elseif ($this->req_type === "UPDATE") {
			foreach ($args as $key => $value) {
				$first_args = sprintf('%s %s.%s=:%s,', $first_args, $this->table, $key, $key);
			}
		} elseif ($this->req_type === "INSERT INTO") {
			foreach ($args as $column => $value) {
				$first_args = sprintf('%s %s,', $first_args, $column);
			}
		}

		return substr($first_args, 1, -1);
	}

	/**
	 * @param array $tags_value [$col_name => $value]
	 */
	private function setTags($tags_value)
	{
		foreach ($tags_value as $tag => $value) {
			$tags[sprintf(':%s', $tag)] = $value;
		}

		return Check::checkArgs($tags);
	}
}










