<?php
namespace App\Access;

use \PDO;

class Connect
{
	private $pdo_connect;
	private $user;
	private $pass;
	private $pdo;

	function __construct($db_host, $db_name, $db_port, $db_user, $db_pass)
	{
		$this->pdo_connect = sprintf('mysql:host=%s;dbname=%s;port=%d', $db_host, $db_name, $db_port);
		$this->user = $db_user;
		$this->pass = $db_pass;
	}

	public function getPdo()
	{
		if (!isset($this->pdo)) {
			try {
				$pdo = new PDO($this->pdo_connect, $this->user, $this->pass);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->pdo = $pdo;
			} catch(PDOException $exception) {
			    die($exception->getMessage());
			}
		}
		return $this->pdo;
	}
}