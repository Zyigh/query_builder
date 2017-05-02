<?php

namespace App\Security;

use App\Core\Setup;

class Check
{
	/**
	 * @param  array $url_get $_GET
	 * @return string          file to require
	 */
	public static function check_url($url_get)
	{
		if (!isset($url_get['page'])) {
			$page = 'home.php';
		} else {
			if ($url_get['page'] === '') {
				header('Location: index.php');
				exit;
			} elseif (!in_array($page, scandir('pages'))) {
				Setup::errorPage();
			} else {
				$page = sprintf('%s.php', $url_get['page']);
			}
		}

		return $page;
	}

	/**
	 * Prevent sql injection
	 * @param  array $args column to select
	 * @return array
	 */
	public static function checkArgs($args)
	{	
		$length = count($args);
		foreach ($args as $key => $value) {
			// $value = str_replace(' ', '', $value);
			$args[$key] = str_replace('\\', '/', $value);
		}

		// var_dump($args);
		// die();
		return $args;
	}
}