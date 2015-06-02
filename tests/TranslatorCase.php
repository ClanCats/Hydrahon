<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 */

use ClanCats\Hydrahon\Builder;

abstract class TranslatorCase extends \PHPUnit_Framework_TestCase
{
	protected $grammar = null;

	/**
	 * Returns an new query builder
	 * 
	 * @return ClanCats\Hydrahon\Builder
	 */
	protected function createBuilder()
	{
		return new Builder( $this->grammar, function( $query, $queryString, $queryParameters )
		{
			return array( $queryString, $queryParameters );
		});
	}

	/**
	 * Asserts the attributes of the given query
	 * 
	 * @param callable 					$query
	 * @param string 					$queryString
	 * @param array 					$queryParameters
	 * @return void
	 */
	protected function assertQueryTranslation($expectedQueryString, $expectedQueryParameters, $callback)
	{
		$builder = $this->createBuilder();

		$query = call_user_func_array($callback, array($builder));

		list($queryString, $queryParameters) = $query->execute();

		$this->assertEquals($expectedQueryString, $queryString);
		$this->assertEquals($expectedQueryParameters, $queryParameters);
	}
}