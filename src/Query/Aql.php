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