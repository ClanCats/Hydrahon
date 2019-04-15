<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @link      https://github.com/ClanCats/Hydrahon/
 * @copyright Copyright (c) 2015-2019 Mario Döring
 * @license   https://github.com/ClanCats/Hydrahon/blob/master/LICENSE (MIT License)
 */

class Update extends SelectBase
{
    /**
     * values container
     *
     * @var array 
     */
    public $values = [];
    
    /**
     * Add set values to the update query
     *
     *     ->set('name', 'Luca')
     * 
     * @param string|array          $param1
     * @param mixed                 $param2
     * @return self
     */
    public function set($param1, $param2 = null): self
    {
        // do nothing if we get nothing
        if (empty($param1))
        {
            return $this;
        }
        
        // when param 2 is not null we assume that only one set is passed
        // like: set( 'name', 'Lu' ); instead of set( array( 'name' => 'Lu' ) );
        if (!is_null($param2))
        {
            $param1 = [$param1 => $param2];
        }
        
        // merge the new values with the existing ones.
        $this->values = array_merge($this->values, $param1); 
        
        // return self so we can continue running the next function
        return $this;
    }
}
