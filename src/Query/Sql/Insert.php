<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

class Insert extends Base
{
    /**
     * values container
     *
     * @var array 
     */
    protected $values = array();
    
    /**
     * make an ignore insert
     *
     * @var bool 
     */
    protected $ignore = false;
    
    /**
     * Insert ignore setter
     *
     *     ->ignore(true)
     * 
     * @param bool                  $ignore
     * @return static The current query builder.
     */
    public function ignore($ignore = true)
    {
        $this->ignore = $ignore; return $this;
    }
    
    /**
     * Add values to the insert
     *
     *     ->values(['name' => 'Mario', 'age' => 42])
     *     
     *     // you can also add multiple rows 
     *     ->values([
     *          ['name' => 'Patrick', 'age' => 24],
     *          ['name' => 'Valentin', 'age' => 21]
     *     ])
     * 
     * @param array                     $values The data you want to insert.
     * @return static The current query builder.
     */
    public function values(array $values = array())
    {
        // do nothing if we get nothing
        if (empty($values))
        {
            return $this;
        }
        
        // check if the passed array is a collection.
        // because we want to be able to insert bulk values.
        if (!is_array(reset( $values )))
        {
            $values = array($values);
        }
        
        // because we could recive the arrays in diffrent order 
        // we have to sort them by their key.
        foreach($values as $key => $value)
        {
            ksort( $value ); $values[$key] = $value;
        }
        
        // merge the new values with the existing ones.
        $this->values = array_merge($this->values, $values); 
        
        // return self so we can continue running the next function
        return $this;
    }

    /**
     * Resets the current insert values of the query.
     *
     * @return static The current query builder.
     */
    public function resetValues()
    {
        $this->values = array(); return $this;
    }
}
