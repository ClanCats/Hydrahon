<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\Query\Expression;

use ClanCats\Hydrahon\BaseQuery;

class Select extends SelectBase implements FetchableInterface
{
    /**
     * fields to be selected
     *
     * @var array
     */
    protected $fields = array();

    /**
     * make a distinct selection
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * order by container
     *
     * @var array
     */
    protected $orders = array();

    /**
     * group by container
     *
     * @var array
     */
    protected $groups = array();

    /**
     * The query having statements
     * 
     * @var array
     */
    protected $havings = array();

    /**
     * join container
     *
     * @var array
     */
    protected $joins = array();

    /**
     * group the results by a given key
     *
     * @var false|string
     */
    protected $groupResults = false;

    /**
     * Forward a value as key
     *
     * @var false|string
     */
    protected $forwardKey = false;

    /**
     * Inherit property values from parent query
     * 
     * @param BaseQuery             $parent
     * @return void
     */
    protected function inheritFromParent(BaseQuery $parent)
    {
        parent::inheritFromParent($parent);

        if ($parent instanceof Select) {
            $parent->copyTo($this);
        } 
    }

    /**
     * Copy current queries select attributes to the given one 
     *
     * @param Select            $query
     */
    public function copyTo(Select $query)
    {
        $query->fields = $this->fields;
        $query->distinct = $this->distinct;
        $query->orders = $this->orders;
        $query->groups = $this->groups;
        $query->havings = $this->havings;
        $query->joins = $this->joins;
        $query->groupResults = $this->groupResults;
        $query->forwardKey = $this->forwardKey;
    }

    /**
     * Distinct select setter
     *
     * @param bool        $distinct
     * @return self The current query builder.
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct; return $this;
    }

    /**
     * Set the selected fields fields
     * 
     *     ->fields('title')
     * 
     *     ->fields(['id', 'name'])
     *     
     *     ->fields('id, name, created_at as created')
     *
     * @param array         $values
     * @return self The current query builder.
     */
    public function fields($fields)
    {
        // we always have to reset the fields
        $this->fields = array();

        // when a string is given
        if (is_string($fields)) 
        {
            $fields = $this->stringArgumentToArray($fields);
        }
        // it also could be an object
        elseif (is_object($fields))
        {
            return $this->addField($fields);
        }

        // do nothing if we get nothing
        if (empty($fields) || $fields === array('*') || $fields === array('')) { return $this; }

        // add the fields
        foreach($fields as $key => $field)
        {
            // when we have a string as key we have an alias definition
            if (is_string($key))
            {
                $this->addField($key, $field);
            } else {
                $this->addField($field);
            }
        }

        return $this;
    }

    /**
     * Add a single select field
     * 
     *     ->addField('title')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addField($field, $alias = null)
    {
        $this->fields[] = array($field, $alias); return $this;
    }

    /**
     * Shortcut to add a count function
     * 
     *     ->addFieldCount('id')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldCount($field, $alias = null)
    {
        $this->addField(new Func('count', $field), $alias); return $this;
    }

    /**
     * Shortcut to add a max function
     * 
     *     ->addFieldMax('views')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldMax($field, $alias = null)
    {
        $this->addField(new Func('max', $field), $alias); return $this;
    }

    /**
     * Shortcut to add a min function
     * 
     *     ->addFieldMin('views')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldMin($field, $alias = null)
    {
        $this->addField(new Func('min', $field), $alias); return $this;
    }

    /**
     * Shortcut to add a sum function
     * 
     *     ->addFieldSum('views')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldSum($field, $alias = null)
    {
        $this->addField(new Func('sum', $field), $alias); return $this;
    }

    /**
     * Shortcut to add a avg function
     * 
     *     ->addFieldAvg('views')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldAvg($field, $alias = null)
    {
        $this->addField(new Func('avg', $field), $alias); return $this;
    }

    /**
     * Shortcut to add a price function
     * 
     *     ->addFieldRound('price')
     *
     * @param string                $field
     * @param string                $alias
     * @return self The current query builder.
     */
    public function addFieldRound($field, $decimals = 0, $alias = null)
    {
        $this->addField(new Func('round', $field, new Expression((int)$decimals)), $alias); return $this;
    }

    /**
     * Add an order by statement to the current query
     * 
     *     ->orderBy('created_at')
     *     ->orderBy('modified_at', 'desc')
     *     
     *     // multiple order statements
     *     ->orderBy(['firstname', 'lastname'], 'desc')
     * 
     *     // muliple order statements with diffrent directions
     *     ->orderBy(['firstname' => 'asc', 'lastname' => 'desc'])
     *
     * @param array|string              $cols
     * @param string                    $order
     * @return self The current query builder.
     */
    public function orderBy($columns, $direction = 'asc')
    {
        if (is_string($columns))
        {
            $columns = $this->stringArgumentToArray($columns);
        }
        elseif ($columns instanceof Expression)
        {
            $this->orders[] = array($columns, $direction); return $this;
        }
        
        foreach ($columns as $key => $column) 
        {
            if (is_numeric($key)) 
            {
                if ($column instanceof Expression)
                {
                    $this->orders[] = array($column, $direction);
                } else {
                    $this->orders[$column] = $direction;
                }
            } else {
                $this->orders[$key] = $column;
            }
        }

        return $this;
    }

    /**
     * Add a group by statement to the current query
     * 
     *     ->groupBy('category')
     *     ->gorupBy(['category', 'price'])
     *
     * @param array|string              $keys
     * @return self The current query builder.
     */
    public function groupBy($groupKeys)
    {
        if (is_string($groupKeys))
        {
            $groupKeys = $this->stringArgumentToArray($groupKeys);
        }

        foreach ($groupKeys as $groupKey) 
        {
            $this->groups[] = $groupKey;
        }

        return $this;
    }

    /**
     * Create a having statement
     *
     *     ->having('name', 'ladina')
     *     ->having('age', '>', 18)
     *     ->having('name', 'in', array('charles', 'john', 'jeffry'))
     *
     * @param string|array      $column The SQL column or an array of column => value pairs.
     * @param mixed             $param1 Operator or value depending if $param2 isset.
     * @param mixed             $param2 The value if $param1 is an opartor.
     * @param string            $type the where type ( and, or )
     *
     * @return self The current query builder.
     */
    public function having($column, $param1 = null, $param2 = null, $type = 'and')
    {
        return $this->appendConditional('having', $column, $param1, $param2, $type);
    }

    /**
     * Create an or having statement
     *
     * This is the same as the normal having just with a fixed type
     *
     * @param string        $column            The SQL column
     * @param mixed        $param1
     * @param mixed        $param2
     *
     * @return self The current query builder.
     */
    public function orHaving($column, $param1 = null, $param2 = null)
    {
        return $this->having($column, $param1, $param2, 'or');
    }

    /**
     * Create an and having statement
     *
     * This is the same as the normal having just with a fixed type
     *
     * @param string        $column            The SQL column
     * @param mixed        $param1
     * @param mixed        $param2
     *
     * @return self The current query builder.
     */
    public function andHaving($column, $param1 = null, $param2 = null)
    {
        return $this->having($column, $param1, $param2, 'and');
    }

    /**
     * Creates a having in statement
     * 
     *     ->havingIn('id', [42, 38, 12])
     * 
     * @param string                    $column
     * @param array                     $options
     * @return self The current query builder.
     */
    public function havingIn($column, array $options = array())
    {
        // when the options are empty we skip
        if ( empty( $options ) )
        {
            return $this;
        }

        return $this->having($column, 'in', $options);
    }

    /**
     * Creates a having in statement
     * 
     *     ->havingIn('id', [42, 38, 12])
     * 
     * @param string                    $column
     * @param array                     $options
     * @return self The current query builder.
     */
    public function havingNotIn($column, array $options = array())
    {
        // when the options are empty we skip
        if ( empty( $options ) )
        {
            return $this;
        }

        return $this->having($column, 'not in', $options);
    }

    /**
     * Creates a having something is null statement
     * 
     *     ->havingNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function havingNull($column)
    {
        return $this->having($column, 'is', $this->raw('NULL'));
    }

     /**
     * Creates a having something is not null statement
     * 
     *     ->havingNotNull('created_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function havingNotNull($column)
    {
        return $this->having($column, 'is not', $this->raw('NULL'));
    }

    /**
     * Creates a or having something is null statement
     * 
     *     ->orHavingNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orHavingNull($column)
    {
        return $this->orHaving($column, 'is', $this->raw('NULL'));
    }

    /**
     * Creates a or having something is not null statement
     * 
     *     ->orHavingNotNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orHavingNotNull($column)
    {
        return $this->orHaving($column, 'is not', $this->raw('NULL'));
    }

    /**
     * Will reset the current selects having conditions
     * 
     * @return self The current query builder.
     */
    public function resetHavings()
    {
        $this->havings = array(); return $this;
    }

    /**
     * Add a join statement to the current query
     * 
     *     ->join('avatars', 'users.id', '=', 'avatars.user_id')
     *
     * @param array|string              $table The table to join. (can contain an alias definition.)
     * @param string                    $localKey 
     * @param string                    $operator The operator (=, !=, <, > etc.)
     * @param string                    $referenceKey
     * @param string                    $type The join type (inner, left, right, outer)
     * 
     * @return self The current query builder.
     */
    public function join($table, $localKey, $operator = null, $referenceKey = null, $type = 'left')
    {
        // validate the join type
        if (!in_array($type, array('inner', 'left', 'right', 'outer')))
        {
            throw new Exception('Invalid join type "'.$type.'" given. Available type: inner, left, right, outer');
        }

        // to make nested joins possible you can pass an closure
        // wich will create a new query where you can add your nested wheres
        if (is_object($localKey) && ($localKey instanceof \Closure)) 
        {
            // create new query object
            $subquery = new SelectJoin;

            // run the closure callback on the sub query
            call_user_func_array($localKey, array(&$subquery));
    
            // add the join
            $this->joins[] = array($type, $table, $subquery); return $this;
        }

        $this->joins[] = array($type, $table, $localKey, $operator, $referenceKey); return $this;
    }

    /**
     * Left join same as join with special type
     *
     * @param array|string              $table The table to join. (can contain an alias definition.)
     * @param string                    $localKey
     * @param string                    $operator The operator (=, !=, <, > etc.)
     * @param string                    $referenceKey
     * 
     * @return self The current query builder.
     */
    public function leftJoin($table, $localKey, $operator = null, $referenceKey = null)
    {
        return $this->join($table, $localKey, $operator, $referenceKey, 'left');
    }

    /**
     * Alias of the `join` method with join type right.
     *
     * @param array|string              $table The table to join. (can contain an alias definition.)
     * @param string                    $localKey
     * @param string                    $operator The operator (=, !=, <, > etc.)
     * @param string                    $referenceKey
     * 
     * @return self The current query builder.
     */
    public function rightJoin($table, $localKey, $operator = null, $referenceKey = null)
    {
        return $this->join($table, $localKey, $operator, $referenceKey, 'right');
    }

    /**
     * Alias of the `join` method with join type inner.
     *
     * @param array|string              $table The table to join. (can contain an alias definition.)
     * @param string                    $localKey
     * @param string                    $operator The operator (=, !=, <, > etc.)
     * @param string                    $referenceKey
     * 
     * @return self The current query builder.
     */
    public function innerJoin($table, $localKey, $operator = null, $referenceKey = null)
    {
        return $this->join($table, $localKey, $operator, $referenceKey, 'inner');
    }

    /**
     * Alias of the `join` method with join type outer.
     *
     * @param array|string              $table The table to join. (can contain an alias definition.)
     * @param string                    $localKey
     * @param string                    $operator The operator (=, !=, <, > etc.)
     * @param string                    $referenceKey
     * 
     * @return self The current query builder.
     */
    public function outerJoin($table, $localKey, $operator = null, $referenceKey = null)
    {
        return $this->join($table, $localKey, $operator, $referenceKey, 'outer');
    }

    /**
     * Forward a result value as array key
     *
     * @param string|bool        $key
     * @return self The current query builder.
     */
    public function forwardKey($key = true)
    {
        if ($key === false) {
            $this->forwardKey = false;
        } elseif ($key === true) {
            $this->forwardKey = \ClanCats::$config->get('database.default_primary_key', 'id');
        } else {
            $this->forwardKey = $key;
        }

        return $this;
    }

    /**
     * Group results by a column
     *
     * example:
     *     array( 'name' => 'John', 'age' => 18, ),
     *     array( 'name' => 'Jeff', 'age' => 32, ),
     *     array( 'name' => 'Jenny', 'age' => 18, ),
     * To:
     *     '18' => array(
     *          array( 'name' => 'John', 'age' => 18 ),
     *          array( 'name' => 'Jenny', 'age' => 18 ),
     *     ),
     *     '32' => array(
     *          array( 'name' => 'Jeff', 'age' => 32 ),
     *     ),
     *
     * @param string|bool        $key
     * @return self The current query builder.
     */
    public function groupResults($key)
    {
        if ($key === false) {
            $this->groupResults = false;
        } else {
            $this->groupResults = $key;
        }

        return $this;
    }

    /**
     * Executes the `executeResultFetcher` callback and handles the results.
     * 
     * @return mixed The fetched result.
     */
    public function get()
    {
         // run the callbacks to retirve the results
        $results = $this->executeResultFetcher();

        // we always exprect an array here!
        if (!is_array($results) || empty($results))
        {
            $results = array();
        }

        // In case we should forward a key means using a value
        // from every result as array key.
        if ((!empty($results)) && $this->forwardKey !== false && is_string($this->forwardKey)) 
        {
            $rawResults = $results;
            $results = array();

            // check if the collection is beeing fetched 
            // as an associated array 
            if (!is_array(reset($rawResults)))
            {
                throw new Exception('Cannot forward key, the result is no associated array.');
            }

            foreach ($rawResults as $result) 
            {
                $results[$result[$this->forwardKey]] = $result;
            }
        }

        // Group the resuls by a items value
        if ((!empty($results)) && $this->groupResults !== false && is_string($this->groupResults)) 
        {
            $rawResults = $results;
            $results = array();

            // check if the collection is beeing fetched 
            // as an associated array 
            if (!is_array(reset($rawResults)))
            {
                throw new Exception('Cannot forward key, the result is no associated array.');
            }

            foreach ($rawResults as $key => $result) 
            {
                $results[$result[$this->groupResults]][$key] = $result;
            }
        }

        // when the limit is specified to exactly one result we
        // return directly that one result instead of the entire array
        if ($this->limit === 1) 
        {
            $results = reset($results);
        }

        return $results;
    }

    /**
     * Executes the 'executeResultFetcher' callback and handles the results
     *
     * @param string         $handler
     * @return mixed
     */
    public function run()
    {
        // run is basically ported from CCF, laravels `get` just feels 
        // much better so lets move on...
        trigger_error('The `run` method is deprecated, `get` method instead.', E_USER_DEPRECATED);

        // run the get method
        return $this->get();
    }

    /**
     * Sets the limit to 1, executes and returns the first result using get.
     *
     * @return mixed The single result.
     */
    public function one()
    {
        return $this->limit(0, 1)->get();
    }

    /**
     * Find something, means select one item by key
     *
     * @param int               $id
     * @param string            $key
     * @return mixed
     */
    public function find($id, $key = 'id')
    {
        return $this->where($key, $id)->one();
    }

    /**
     * Get the first result orderd by the given key.
     *
     * @param string            $key By what should the first item be selected? Default is: 'id'
     * @return mixed The first result.
     */
    public function first($key = 'id')
    {
        return $this->orderBy($key, 'asc')->one();
    }

    /**
     * Get the last result by key
     *
     * @param string            $key
     * @param string            $name
     * @return mixed the last result.
     */
    public function last($key = 'id')
    {
        return $this->orderBy($key, 'desc')->one();
    }

    /**
     * Just get a single value from the result
     *
     * @param string            $column The name of the column.
     * @return mixed The columns value
     */
    public function column($column)
    {
        $result = $this->fields($column)->one(); 

        // only return something if something is found
        if (is_array($result))
        {
            return reset($result);
        }
    }

    /**
     * Just return the number of results 
     *
     * @param string                    $field
     * @return int
     */
    public function count($field = null)
    {
        // when no field is given we use *
        if (is_null($field))
        {
            $field = new Expression('*');
        }

        // return the column
        return (int) $this->column(new Func('count', $field));
    }

    /**
     * Helper for the SQL sum aggregation.
     *
     * @param string            $field
     * @return int
     */
    public function sum($field)
    {
        return $this->column(new Func('sum', $field));
    }

    /**
     * Helper for the SQL max aggregation.
     *
     * @param string            $field
     * @return int
     */
    public function max($field)
    {
        return $this->column(new Func('max', $field));
    }

    /**
     * Helper for the SQL min aggregation.
     *
     * @param string            $field
     * @return int
     */
    public function min($field)
    {
        return $this->column(new Func('min', $field));
    }

    /**
     * Helper for the SQL average aggregation.
     *
     * @param string            $field
     * @return int
     */
    public function avg($field)
    {
        return $this->column(new Func('avg', $field));
    }

    /** 
     * Do any results of this query exist?
     * 
     * @return bool
     */
    public function exists()
    {
        $existsQuery = new Exists($this);

        // set the current select for the exists query
        $existsQuery->setSelect($this);

        // run the callbacks to retirve the results
        $result = $existsQuery->executeResultFetcher();

        if (isset($result[0]['exists']))
        {
            return (bool) $result[0]['exists'];
        }
        
        return false;
    }
}
