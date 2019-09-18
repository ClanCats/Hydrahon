<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * Select base 
 * 
 * Implements common filtering functions like: where, limit and offset
 * 
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class SelectBase extends Base
{ 
    /**
     * The query where statements
     *
     * @var array
     */
    protected $wheres = array();

    /**
     * The query having statements
     * 
     * @var array
     */
    protected $havings = array();

    /**
     * the query offset
     *
     * @var int
     */
    protected $offset = null;

    /**
     * the query limit
     *
     * @var int
     */
    protected $limit = null;

    /**
     * Returns an string argument as parsed array if possible
     * 
     * @param string                $argument
     * @return array
     */
    protected function stringArgumentToArray($argument)
    {
        if ( strpos($argument, ',') !== false )
        {
            return array_map('trim', explode(',', $argument));
        }

        return array($argument);
    }

    /**
     * Will reset the current selects where conditions
     * 
     * @return self The current query builder.
     */
    public function resetWheres()
    {
        $this->wheres = array(); return $this;
    }

    /**
     * Will reset the current selects limit
     * 
     * @return self The current query builder.
     */
    public function resetLimit()
    {
        $this->limit = null; return $this;
    }

    /**
     * Will reset the current selects offset
     * 
     * @return self The current query builder.
     */
    public function resetOffset()
    {
        $this->offset = null; return $this;
    }

    /**
     * Create a where statement
     *
     *     ->where('name', 'ladina')
     *     ->where('age', '>', 18)
     *     ->where('name', 'in', array('charles', 'john', 'jeffry'))
     *
     * @param string            $column The SQL column
     * @param mixed             $param1 Operator or value depending if $param2 isset.
     * @param mixed             $param2 The value if $param1 is an opartor.
     * @param string            $type the where type ( and, or )
     *
     * @return self The current query builder.
     */
    public function where($column, $param1 = null, $param2 = null, $type = 'and')
    {
        // if this is the first where element we are going to change
        // the where type to 'where'
        if (empty($this->wheres)) 
        {
            $type = 'where';
        }
        elseif($type === 'where')
        {
            $type = 'and';
        }

        // when column is an array we assume to make a bulk and where.
        if (is_array($column)) 
        {
            $subquery = new SelectBase;
            foreach ($column as $key => $val) 
            {
                $subquery->where($key, $val, null, $type);
            }

            $this->wheres[] = array($type, $subquery);
            return $this;
        }

        // Add the condition
        $this->wheres[] = $this->parseConditional($column, $param1, $param2, $type);

        return $this;
    }

    /**
     * Parse the parameters for methods that build conditional statements ( where, having )
     * 
     * @param string            $column The SQL column
     * @param mixed             $param1
     * @param mixed             $param2
     * @param string            $type
     * @return array            An array that might look like one of this examples:
     *                           [ 'where', 'column', '=', 'value' ]
     *                           [ 'or', 'column', 'in', array(1,2,3) ]
     *                           [ 'and', <SelectBase object> ]
     */
    protected function parseConditional($column, $param1 = null, $param2 = null, $type) {
        // check if the type is valid
        if (!in_array($type, $validTypes = ['and', 'or', 'where', 'having']))
        {
            throw new Exception('Invalid condition type "'.$type.'", must be one of the following: ' . implode(', ', $validTypes));
        }

        // to make nested wheres/havings possible you can pass an closure
        // wich will create a new query where you can add your nested wheres/havings
        if (is_object($column) && ($column instanceof \Closure)) 
        {
            // create new query object
            $subquery = new SelectBase;

            // run the closure callback on the sub query
            call_user_func_array($column, array( &$subquery ));
 
            return array($type, $subquery);
        }

        // when param2 is null we replace param2 with param one as the
        // value holder and make param1 to the = operator.
        if (is_null($param2)) 
        {
            $param2 = $param1; $param1 = '=';
        }

        // if the param2 is an array we filter it. Im no more sure why
        // but it's there since 4 years so i think i had a reason.
        // edit: Found it out, when param2 is an array we probably 
        // have an "in" or "between" statement which has no need for dublicates.
        if (is_array($param2)) 
        {
            $param2 = array_unique($param2);
        }

        return array($type, $column, $param1, $param2);
    }

    /**
     * Create an or where statement
     *
     * This is the same as the normal where just with a fixed type
     *
     * @param string        $column            The SQL column
     * @param mixed        $param1
     * @param mixed        $param2
     *
     * @return self The current query builder.
     */
    public function orWhere($column, $param1 = null, $param2 = null)
    {
        return $this->where($column, $param1, $param2, 'or');
    }

    /**
     * Create an and where statement
     *
     * This is the same as the normal where just with a fixed type
     *
     * @param string        $column            The SQL column
     * @param mixed        $param1
     * @param mixed        $param2
     *
     * @return self The current query builder.
     */
    public function andWhere($column, $param1 = null, $param2 = null)
    {
        return $this->where($column, $param1, $param2, 'and');
    }

    /**
     * Creates a where in statement
     * 
     *     ->whereIn('id', [42, 38, 12])
     * 
     * @param string                    $column
     * @param array                     $options
     * @return self The current query builder.
     */
    public function whereIn($column, array $options = array())
    {
        // when the options are empty we skip
        if ( empty( $options ) )
        {
            return $this;
        }

        return $this->where($column, 'in', $options);
    }

    /**
     * Creates a where something is null statement
     * 
     *     ->whereNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function whereNull($column)
    {
        return $this->where($column, 'is', $this->raw('NULL'));
    }

     /**
     * Creates a where something is not null statement
     * 
     *     ->whereNotNull('created_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function whereNotNull($column)
    {
        return $this->where($column, 'is not', $this->raw('NULL'));
    }

    /**
     * Creates a or where something is null statement
     * 
     *     ->orWhereNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orWhereNull($column)
    {
        return $this->orWhere($column, 'is', $this->raw('NULL'));
    }

    /**
     * Creates a or where something is not null statement
     * 
     *     ->orWhereNotNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orWhereNotNull($column)
    {
        return $this->orWhere($column, 'is not', $this->raw('NULL'));
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
     * Create a having statement
     *
     *     ->having('name', 'ladina')
     *     ->having('age', '>', 18)
     *     ->having('name', 'in', array('charles', 'john', 'jeffry'))
     *
     * @param string            $column The SQL column
     * @param mixed             $param1 Operator or value depending if $param2 isset.
     * @param mixed             $param2 The value if $param1 is an opartor.
     * @param string            $type the where type ( and, or )
     *
     * @return self The current query builder.
     */
    public function having($column, $param1 = null, $param2 = null, $type = 'and')
    {
        // if this is the first having element we are going to change
        // the having type to 'having'
        if (empty($this->havings)) 
        {
            $type = 'having';
        }
        elseif($type === 'having')
        {
            $type = 'and';
        }

        // when column is an array we assume to make a bulk and having.
        if (is_array($column)) 
        {
            $subquery = new SelectBase;
            foreach ($column as $key => $val) 
            {
                $subquery->having($key, $val, null, $type);
            }

            $this->havings[] = array($type, $subquery);
            return $this;
        }

        // Add the condition
        $this->havings[] = $this->parseConditional($column, $param1, $param2, $type);

        return $this;
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
     * Set the query limit
     * 
     *     // limit(<limit>)
     *     ->limit(20)
     * 
     *     // limit(<offset>, <limit>)
     *     ->limit(60, 20)
     *
     * @param int           $limit
     * @param int           $limit2
     * @return self The current query builder.
     */
    public function limit($limit, $limit2 = null)
    {
        if (!is_null($limit2)) 
        {
            $this->offset = (int) $limit;
            $this->limit = (int) $limit2;
        } else {
            $this->limit = (int) $limit;
        }

        return $this;
    }

    /**
     * Set the queries current offset
     * 
     * @param int               $offset
     * @return self The current query builder.
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset; return $this;
    }

    /**
     * Create an query limit based on a page and a page size
     *
     * @param int        $page
     * @param int         $size
     * @return self The current query builder.
     */
    public function page($page, $size = 25)
    {
        if (($page = (int) $page) < 0) 
        {
            $page = 0;
        }

        $this->limit = (int) $size;
        $this->offset = (int) $size * $page;

        return $this;
    }
}
