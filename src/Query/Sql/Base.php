<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * Base sql class
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

class Base extends BaseQuery
{ 
    /**
     * The database the query should be executed on
     * 
     * @var string
     */
    protected $database = null;

    /**
     * The table the query should be executed on
     * 
     * @var string
     */
    protected $table = null;

    /**
     * Inherit property values from parent query
     * 
     * @param BaseQuery             $parent
     * @return void
     */
    protected function inheritFromParent(BaseQuery $parent)
    {
        parent::inheritFromParent($parent);

        if (isset($parent->database)) {
            $this->database = $parent->database;
        }
        if (isset($parent->table)) {
            $this->table = $parent->table;
        }
    }
    
    /**
     * Create a new select query builder
     *      
     *     // selecting a table
     *     $h->table('users')
     *  
     *     // selecting table and database
     *     $h->table('db_mydatabase.posts')
     *
     * @param string                   $table
     * @return self
     */
    public function table($table)
    {
        $database = null;

        // Check if the table is an object, this means
        // we have an subselect inside the table
        if (is_object($table) && ($table instanceof \Closure)) 
        {
            // create new query object
            $subquery = new Select;

            // run the closure callback on the sub query
            call_user_func_array($table, array( &$subquery ));

            $table = $subquery;
        } 

        // other wise normally try to split the table and database name
        elseif (is_string($table) && strpos($table, '.') !== false)
        {
            $selection = explode('.', $table);

            if (count($selection) !== 2)
            {
                throw new Exception( 'Invalid argument given. You can only define one seperator.' );
            }

            list($database, $table) = $selection;
        }

        // the table might include an alias we need to parse that one out 
        if (is_string($table) && strpos($table, ' as ') !== false)
        {
            $tableParts = explode(' as ', $table);
            $table = array($tableParts[0] => $tableParts[1]);
            unset($tableParts);
        }

        // assing the result
        $this->database = $database;
        $this->table = $table;

        // return self
        return $this;
    }
}
