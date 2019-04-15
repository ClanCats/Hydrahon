<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * Exists query 
 * 
 * Allows building queries like "SELECT EXISTS(select * from showtimes) as hasShows"
 **
 * @link      https://github.com/ClanCats/Hydrahon/
 * @copyright Copyright (c) 2015-2019 Mario Döring
 * @license   https://github.com/ClanCats/Hydrahon/blob/master/LICENSE (MIT License)
 */

use ClanCats\Hydrahon\BaseQuery;

class Exists extends BaseQuery implements FetchableInterface
{
	/**
	 * The select query we want to check if 
	 * any results exists
	 * 
	 * @var Select
	 */
    protected $select = null;

    /**
     * Sets the select query
     * 
     * @param Select 				$select
     * @return void
     */
    public function setSelect(Select $select): void
    {
    	$this->select = $select;
    }
}
