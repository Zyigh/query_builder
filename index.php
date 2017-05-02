<?php
use App\Access\Queries\Builder;
// use \PDO;

require_once "vendor/autoload.php";

$build = new Builder('articles');

echo "<pre>";

$update = array('titre' => 'Pipou <3',
				'auteur' => 'Zyigh');

// id=>11
// var_dump($build->getLast('id')->get());


// var_dump($build->count()->get());


var_dump($build->select('titre', 'auteur', 'commentaire')->get());

var_dump($build->update($update)->where('11')->get());



// var_dump($build->select('*')->where(11)->get());

echo "<br>";
