<?php
use App\Access\Queries\Builder;
use App\Core\Test;

require_once "vendor/autoload.php";


$build = new Builder('articles');

echo "<pre>";

$insert = array('titre' => 'insert',
				'auteur' => 'Hugo',
				'commentaire' => 'test sur insert into');



// var_dump($build->delete(14)->get());
$datas = $build->select('id')->get();

var_dump($datas);

echo "<br>";
