<?php namespace ClanCats\Hydrahon;

/**
 * Query translator interface
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

interface TranslatorInterface
{
	/**
	 * Translate the given query object and return the results as 
	 * argument array
	 * 
	 * @param BaseQuery 				$query
	 * @return array
	 */
    public function translate(BaseQuery $query);
}
