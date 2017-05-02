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
	 * @return [type] [description]
	 */
	public function getLast()
	{
		if (!empty(func_get_args())) {
			$this->order = "{$this->table}.id DESC";

			return $this->order(func_get_args());
		}

		return "select smth plzzzz bruuuuuuuh !!!!!!!";
	}

	/**
	 * select column(s) 
	 * @param string  column(s)
	 * @return object $this
	 */
	public function select($args = null)
	{	
		if (!empty(func_get_args())) {
			$this->req_type = "SELECT";
			if (!empty($this->first_args)) {
				$this->first_args .= ', ';
			}
			$this->first_args .= $this->setArgs(func_get_args());

			return $this;
		} else {
			die('select something duuuuuude !!!');
		}
	}

	/**
	 * @param  int $int id de la ligne à delete
	 * @return instance
	 */
	public function delete($int = 0)
	{
		if ($int && is_numeric( $int )) {
			$this->fetchable = false;
			$this->req_type = "DELETE";
			$this->where = "id = :id";
			$this->tags[':id'] = $int;

			return $this;
		}
		die('FUUUUU BRUH !!!');
	}

	/**
	 * @param  array $datas 
	 * @return instance
	 */
	public function update($datas)
	{
		if (!empty( $datas )) {
			$this->fetchable = false;
			$this->fetchAll = false;
			$this->req_type = "UPDATE";
			$datas = Check::checkArgs($datas);
			foreach ($datas as $key => $value) {
				$this->first_args = sprintf('%s,', $key);
				$this->tags[sprintf(':%s', $key)] = $value;
			}
	
			return $this;
		} else {
			die('Tell me wut 2 update bruh... I aint even kiddin');
		}
	}

	public function insert($datas)
	{
		if (!empty( $datas )) {
			$this->req_type = "INSERT INTO";
			$this->fetchable = false;
			foreach ($datas as $key => $value) {
				$this->first_args = sprintf('%s,', $key);
				$this->tags[sprintf(':%s', $key)] = $value;
			}
		}

		return $this;
	}

	/**
	 * @return [type] [description]
	 */
	public function getFirst()
	{
		if (!empty(func_get_args())) {
			$this->order = "{$this->table}.id ASC";

			return $this->order(func_get_args());
		}

		return "select smth plzzzz bruuuuuuuh !!!!!!!";
	}

	/**
	 * @return [type] [description]
	 */
	public function order($args)
	{
		$this->req_type = "SELECT";
		$this->first_args = $this->setArgs($args);
		$this->limit = 1;

		return $this;
	}

	/**
	 * Add a where to sql query
	 * @param  mixed 	$value	
	 * @param  string 	$col 	id if not specified
	 * @return instance
	 */
	public function where($value, $col = 'id')
	{
		// $col = Check::checkArgs($col);
		$this->where = sprintf('%s=:%s', $col, $col);
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
	 * @param  string $limit 
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
		$this->req_type = '';
		$this->first_args = '';
		$this->args = '';
		$this->sql = '';
		$this->stmt = '';
		$this->tags = array();
		$this->order = false;
		$this->limit = false;
		$this->where = false;
		$this->fetchable = true;

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
			$sql_setter = explode(',', $this->first_args);
			$length = count($sql_setter);
			for ($i = 0; $i < $length - 1; $i++) {
				$sql = sprintf('%s %s=:%s, ', $sql, $sql_setter[$i], $sql_setter[$i]);
			}
			$sql = substr($sql, 0, -2);
		} elseif ($this->req_type === "INSERT INTO") {
			$sql = sprintf('%s %s (', $this->req_type, $this->table);

		} else {
			$sql = sprintf('%s %s FROM %s', $this->req_type, $this->first_args, $this->table);
			die($sql);
		}

		if ($this->order) {
			$sql = sprintf('%s ORDER BY %s',$sql, $this->order);
		}
		if ($this->where) {
			$sql = sprintf('%s WHERE %s',$sql, $this->where);
		}
		if ($this->limit) {
			$sql = sprintf('%s LIMIT %s',$sql, $this->limit);
		}
		if ($this->offset) {
			$sql = sprintf('%s OFFSET %s',$sql, $this->offset);
		}

		die($sql);

		return $sql;
	}

	/**
	 * @param string $args first args line
	 */
	private function setArgs($args)
	{
		$count = count($args);
		$args = Check::checkArgs($args);
		$first_args = '';
		for ($i = 0; $i < $count; $i++) {
			$first_args .= sprintf('%s.%s, ',$this->table ,$args[$i]);
		}

		return substr($first_args, 0, -2);
	}
}










