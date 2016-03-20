<?php 

require "vendor/autoload.php";

use ClanCats\Hydrahon\Builder;

$h = new Builder('arango', function( $query, $queryString, $queryParameters )
{
	var_dump($queryString, $queryParameters);
});


$h->each('movie', 'movies')
	->limit(10)
	->filter('movie.year', '>', 2010)
	->execute();


// $h->each('movie', 'movies')
// 	->limit(10)
// 	->filter(function($q) 
// 	{
// 		$q->filter('movie.year', 2015);
// 		$q->orFilter('movie.year', 2016);
// 	})
// 	->execute();