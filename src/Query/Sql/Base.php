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
     * @var string|null
     */
    protected $database = null;

    /**
     * The table the query should be executed on
     * 
     * @var string|null
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
     * @param string|\Closure                   $table
     * @return static
     */
    public function table($table, $alias = null)
    {
        $database = null;

        // Check if the table is an object, this means
        // we have an subselect inside the table
        if (is_object($table) && ($table instanceof \Closure)) 
        {
            // we have to check if an alias isset 
            // otherwise throw an exception to prevent the 
            // "Every derived table must have its own alias" error
            if (is_null($alias))
            {
                throw new Exception('You must define an alias when working with subselects.');
            }

            $table = array($alias => $table);
        } 

        // Check if the $table is an array and the value is an closure
        // that we can pass a new query object as subquery 
        if (is_array($table) && is_object(reset($table)) && (reset($table) instanceof \Closure)) 
        {
            $alias = key($table);
            $table = reset($table);

            // create new query object
            $subquery = new Select;

            // run the closure callback on the sub query
            call_user_func_array($table, array(&$subquery));

            // set the table 
            // IMPORTANT: Only if we have a closure as table
            // we set the alias as key. This might cause some confusion
            // but only this way we can keep the normal ['table' => 'alias'] syntax
            $table = array($alias => $subquery);
        } 

        // otherwise normally try to split the table and database name
        elseif (is_string($table) && strpos($table, '.') !== false)
        {
            $selection = explode('.', $table);

            if (count($selection) !== 2)
            {
                throw new Exception( 'Invalid argument given. You can only define one separator.' );
            }

            list($database, $table) = $selection;
        }

        // the table might include an alias we need to parse that one out 
        if (is_string($table) && strpos($table, ' as ') !== false)
        {
            $tableParts = explode(' as ', $table);
            $table = array($tableParts[0] => $tableParts[1]);
        }
        elseif(is_string($table) && (!is_null($alias)))
        {
            $table = array($table => $alias);
        }

        // assing the result
        $this->database = $database;
        $this->table = $table;

        // return self
        return $this;
    }
}
