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
     * @var array<array>
     */
    protected $wheres = array();

    /**
     * the query offset
     *
     * @var int|null
     */
    protected $offset = null;

    /**
     * the query limit
     *
     * @var int|null
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
     * @return static The current query builder.
     */
    public function resetWheres()
    {
        $this->wheres = array(); return $this;
    }

    /**
     * Will reset the current selects limit
     * 
     * @return static The current query builder.
     */
    public function resetLimit()
    {
        $this->limit = null; return $this;
    }

    /**
     * Will reset the current selects offset
     * 
     * @return static The current query builder.
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
     * @param string|array|\Closure     $column The SQL column or columns.
     * @param mixed                     $param1 Operator or value depending if $param2 isset.
     * @param mixed                     $param2 The value if $param1 is an opartor.
     * @param string                    $type the where type ( and, or )
     *
     * @return static The current query builder.
     */
    public function where($column, $param1 = null, $param2 = null, $type = 'and')
    {
        // check if the where type is valid
        if ($type !== 'and' && $type !== 'or' && $type !== 'where' )
        {
            throw new Exception('Invalid where type "'.$type.'"');
        }

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

            $this->wheres[] = array($type, $subquery); return $this;
        }

        // to make nested wheres possible you can pass an closure
        // wich will create a new query where you can add your nested wheres
        if (is_object($column) && ($column instanceof \Closure)) 
        {
            // create new query object
            $subquery = new SelectBase;

            // run the closure callback on the sub query
            call_user_func_array($column, array( &$subquery ));
 
            $this->wheres[] = array($type, $subquery); return $this;
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
        // have an "in" or "between" statement which has no need for duplicates.
        if (is_array($param2)) 
        {
            $param2 = array_unique($param2);
        }

        $this->wheres[] = array($type, $column, $param1, $param2);

        return $this;
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
     * @return static The current query builder.
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
     * @return static The current query builder.
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
     * @return static The current query builder.
     */
    public function whereIn($column, array $options = array())
    {
        // when the options are empty we skip
        if (empty($options))
        {
            return $this;
        }

        return $this->where($column, 'in', $options);
    }

    /**
     * Creates a where not in statement
     * 
     *     ->whereIn('id', [42, 38, 12])
     * 
     * @param string                    $column
     * @param array                     $options
     * @return static The current query builder.
     */
    public function whereNotIn($column, array $options = array())
    {
        // when the options are empty we skip
        if (empty($options))
        {
            return $this;
        }

        return $this->where($column, 'not in', $options);
    }

    /**
     * Creates a where something is null statement
     * 
     *     ->whereNull('modified_at')
     * 
     * @param string                    $column
     * @return static The current query builder.
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
     * @return static The current query builder.
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
     * @return static The current query builder.
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
     * @return static The current query builder.
     */
    public function orWhereNotNull($column)
    {
        return $this->orWhere($column, 'is not', $this->raw('NULL'));
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
     * @return static The current query builder.
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
     * @return static The current query builder.
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset; return $this;
    }

    /**
     * Create a query limit based on a page and a page size
     *
     * @param int        $page
     * @param int         $size
     * @return static The current query builder.
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
