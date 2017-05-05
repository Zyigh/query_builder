<?php

namespace App\Security;

use App\Core\Setup;

class Check
{
	/**
	 * Prevent sql injection
	 * @param  array $args column to select
	 * @return array
	 */
	public static function checkArgs($args)
	{	
		$length = count($args);
		foreach ($args as $key => $value) {
			$args[$key] = trim($value);
			$args[$key] = htmlentities($value);
			$args[$key] = str_replace('\\', '/', $value);
		}

		return $args;
	}
}