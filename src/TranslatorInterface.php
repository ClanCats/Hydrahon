<?php 

namespace ClanCats\Hydrahon;

/**
 * Query translator interface
 **
 * @link      https://github.com/ClanCats/Hydrahon/
 * @copyright Copyright (c) 2015-2019 Mario Döring
 * @license   https://github.com/ClanCats/Hydrahon/blob/master/LICENSE (MIT License)
 */

interface TranslatorInterface
{
	/**
	 * Translate the given query object and return the results as 
	 * argument array
	 * 
	 * @param ClanCats\Hydrahon\BaseQuery 				$query
	 * @return array
	 */
    public function translate(BaseQuery $query);
}
