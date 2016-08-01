<?php namespace ClanCats\Hydrahon\Query;

/**
 * AQL query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;
use ClanCats\Hydrahon\Aql\Exception;

class Aql extends BaseQuery
{
    /**
     * For loop item
     *
     * @var string
     */
    protected $for = null;

    /**
     * For loop collection
     *
     * @var string|object
     */
    protected $in = null;

    /**
     * the return statement
     *
     * @var string|object
     */
    protected $return = null;

    /**
     * The filters
     *
     * @var array()
     */
    protected $filters = null;

    /**
     * Subquery
     *
     * @var string|object
     */
    protected $subquery = null;

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
     * For is a reserved keyword so we have to go 
     * with for...
     * 
     * @param string            $item
     * @param mixed             $collection
     * @return self
     */
    public function each($item, $collection = null, $scope = null)
    { 
        // if the scope is set create a subquery
        if (!is_null($scope))
        {
            $this->subquery = new static($this);
            $this->subquery->each($item, $collection);
            
            // run the callback
            $this->subquery->call($scope);
            
            // return self
            return $this;
        }

        // if the collection is null we expect to have
        // an `as` or `an` inside the item string
        if (is_null($collection))
        {
            if (strpos($item, ' in ') !== false)
            {
                list($item, $collection) = explode(' in ', $item);
            }
            elseif (strpos($item, ' as ') !== false)
            {
                list($collection, $item) = explode(' as ', $item);
            }
            else
            {
                throw new Exception('Connot create for / each loop without a collection parameter.');
            }
        }

        // assing item and collection
        $this->for = $item;
        $this->in = $collection;

        return $this;
    }

    /**
     * Set the return value
     * 
     * @param string|object
     * @return self
     */
    public function get($return)
    {
        $this->return = $return; return $this;
    }

    /**
     * Alias of the return method but the value will not be escaped
     * 
     * @param string
     * @return self
     */
    public function returnRaw($return)
    {
        return $this->return($this->raw($return));
    }

    /**
     * Create a filter statement
     *
     * example:
     *     ->filter('movie.title', 'Moon')
     *     ->filter('user.age', '>', 18)
     *     ->filter('post.category', 'in', array('css', 'javascript'))
     *     ->filter('post.category', 'not in', 'user.hiddenCategories', false)
     *
     * @param string            $column             The SQL column
     * @param mixed             $param1
     * @param mixed             $param2
     * @param bool              $parameterize       Shold the given value be parameterized?
     * @param string            $type               The where type ( and, or )
     *
     * @return self
     */
    public function filter($column, $param1 = null, $param2 = null, $parameterize = true, $type = 'and')
    {
        // check if the where type is valid
        if ($type !== 'and' && $type !== 'or')
        {
            throw new Exception('Invalid filter type "'.$type.'"');
        }

        // when column is an array we assume to make a bulk and where.
        if (is_array($column)) 
        {
            $subquery = new static;
            foreach ($column as $key => $val) 
            {
                $subquery->filter($key, $val, null, $parameterize, $type);
            }

            $this->filters[] = array($type, $parameterize, $subquery); return $this;
        }

        // to make nested wheres possible you can pass an closure
        // wich will create a new query where you can add your nested wheres
        if (is_object($column) && ($column instanceof \Closure)) 
        {
            // create new query object
            $subquery = new static;

            // run the closure callback on the sub query
            call_user_func_array($column, array(&$subquery));
 
            $this->filters[] = array($type, $parameterize, $subquery); return $this;
        }

        // when param2 is null we replace param2 with param one as the
        // value holder and make param1 to the = operator.
        if (is_null($param2)) 
        {
            $param2 = $param1; $param1 = '==';
        }

        // Remove dublicated items for in statements
        if (is_array($param2)) {
            $param2 = array_unique($param2);
        }

        // add a normal filter
        $this->filters[] = array($type, $parameterize, $column, $param1, $param2);

        return $this;
    }

    /**
     * Set the query limit and offset
     * 
     *     // limit(<limit>)
     *     ->limit(20)
     * 
     *     // limit(<offset>, <limit>)
     *     ->limit(60, 20)
     *
     * @param int           $offset
     * @param int           $limit
     * @return self
     */
    public function limit($offset, $limit = null)
    {
        if (!is_null($limit)) 
        {
            $this->offset = (int) $offset;
            $this->limit = (int) $limit;
        } else {
            $this->limit = (int) $offset;
        }

        return $this;
    }

    /**
     * Set the queries current offset
     * 
     * @param int               $offset
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