<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class Update extends SelectBase
{
    /**
     * values container
     *
     * @var array 
     */
    public $values = array();
    
    /**
     * Add set values to the update query
     *
     *     ->set('name', 'Luca')
     * 
     * @param string|array          $param1
     * @param mixed                 $param2
     * @return self
     */
    public function set($param1, $param2 = null)
    {
        // do nothing if we get nothing
        if (empty($param1))
        {
            return $this;
        }
        
        // when param 2 is not null we assume that only one set is passed
        // like: set( 'name', 'Lu' ); instead of set( array( 'name' => 'Lu' ) );
        if ( !is_null( $param2 ) )
        {
            $param1 = array( $param1 => $param2 );
        }
        
        // merge the new values with the existing ones.
        $this->values = array_merge( $this->values, $param1 ); 
        
        // return self so we can continue running the next function
        return $this;
    }
}
