<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * Base sql class
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

class BaseSql extends BaseQuery
{
    /**
     * The query where statements
     *
     * @var array
     */
    protected $wheres = array();

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
     * @param string 				$argument
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
     * Create a where statement
     *
     * where query: <$column> <$param1> <$param2> <$type>
     *
     * example:
     *     ->where('name', 'ladina')
     *     ->where('age', '>', 18)
     *     ->where('name', 'in', array('johanna', 'jennifer'))
     *
     * @param string      		$column            	The SQL column
     * @param mixed       		$param1
     * @param mixed        		$param2
     * @param string        	$type            	The where type ( and, or )
     *
     * @return self
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
        	$subquery = new BaseSql;
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
        	$subquery = new BaseSql;

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
        // have an "in" or "between" statement which has no need for dublicates.
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
     * @return self
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
     * @return self
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
     * @param string 					$column
     * @param array 					$options
     * @return self
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
     * @param string 					$column
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function orWhereNotNull($column)
    {
        return $this->orWhere($column, 'is not', $this->raw('NULL'));
    }

    /**
     * Set the query limit
     * 
     * 	   // limit( <limit> )
     *     ->limit( 20 )
     * 
     *     // limit( <offset>, <limit> )
     *     ->limit( 60, 20 )
     *
     * @param int        	$limit
     * @param int         	$limit2
     * @return self
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
     * @param int 				$offset
     * @return self
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
     * @return self
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
