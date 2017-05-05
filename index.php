<?php
use App\Access\Queries\Builder;
// use \PDO;

require_once "vendor/autoload.php";

$build = new Builder('articles');

echo "<pre>";

$insert = array('titre' => 'insert',
				'auteur' => 'Hugo',
				'commentaire' => 'test sur insert into');

var_dump($build->getLast()->get());
// var_dump($build->delete(15)->get());
die();

// var_dump($build->delete(14)->get());
$datas = $build->select('titre', 'auteur', 'commentaire')->get();

var_dump($datas);

echo "<br>";
