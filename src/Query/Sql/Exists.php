<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * Exists query 
 * 
 * Allows building queries like "SELECT EXISTS(select * from showtimes) as hasShows"
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
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
    public function setSelect(Select $select)
    {
    	$this->select = $select;
    }
}
