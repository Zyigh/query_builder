<?php
namespace App\Core;

use App\Access\Connect;

class Setup
{
	CONST DB_NAME = 'pipou';
	CONST DB_HOST = 'localhost';
	CONST DB_USER = 'root';
	CONST DB_PASS = 'root';
	CONST DB_PORT = '8888';

	/**
	 * @var \PDO
	 */
	private static $connexion;

	public static function getConnexion()
	{
		if (!isset(self::$connexion)) {
			self::$connexion = new Connect(self::DB_HOST, self::DB_NAME, self::DB_PORT, self::DB_USER, self::DB_PASS);
		}

		return self::$connexion->getPdo();
	}

	public static function errorPage()
	{
		header("HTTP/1.0 404 Not Found");

    	return "404.php";
	}
}