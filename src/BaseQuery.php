<?php namespace ClanCats\Hydrahon;

/**
 * Base query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\Query\Expression;

class BaseQuery
{   
    /**
     * Query builder callback macros
     * 
     * @var array<callable>
     */
    protected $macros = array();

    /**
     * Query flags
     * These allow you to store data inside the query object.
     * This data has no influence on the generated query string or parameters directly.
     * But allow you to use the query a state mashine.
     *  
     * @var array<mixed>
     */
    protected $flags = array();

    /**
     * The callback where we fetch the results from
     *
     * @var callable|null
     */
    protected $resultFetcher = null;

    /**
     * Construct new query object and inherit properties
     *
     * @param BaseQuery             $parent
     * @return void
     */
    final public function __construct(BaseQuery $parent = null)
    {
        if (!is_null($parent))
        {
            $this->inheritFromParent($parent);
        }
    }

    /**
     * Inherit property values from parent query
     * 
     * @param BaseQuery             $parent
     * @return void
     */
    protected function inheritFromParent(BaseQuery $parent)
    {
        $this->macros = $parent->macros;
        $this->flags = $parent->flags;
        $this->resultFetcher = $parent->resultFetcher;   
    }

    /**
     * Set the result fetcher of the query
     *
     * @param callable              $resultFetcher
     * @return void
     */
    public function setResultFetcher($resultFetcher = null)
    {
        $this->resultFetcher = $resultFetcher;
    }

    /**
     * Set a flag on the query object
     *
     * @param string            $key
     * @param mixed             $value
     * @return void
     */
    final public function setFlag($key, $value)
    {
        $this->flags[$key] = $value;
    }

    /**
     * Gets a flag from the query object
     *
     * @param string            $key
     * @param mixed             $default
     * @return mixed
     */
    final public function getFlag($key, $default = null)
    {
        if (!isset($this->flags[$key])) {
            return $default;
        }

        return $this->flags[$key];
    }

    /**
     * Register a macro on the current query object
     * 
     * @param string                $method
     * @param callable             $callback
     * @return void
     */
    final public function macro($method, $callback)
    {
        $this->macros[$method] = $callback;
    }

    /**
     * Allow macro calls 
     * 
     * @param string                       $name
     * @param array<mixed>                 $arguments
     * @return mixed
     */ 
    public function __call($name, $arguments) 
    {
        if (!isset($this->macros[$name]))
        {
            throw new \BadMethodCallException('There is no macro or method with the name "'.$name.'" registered.');
        }

        call_user_func_array($this->macros[$name], array_merge(array(&$this), $arguments)); return $this;
    }

    /**
     * Pass the own query to a callback
     * 
     * @param callable              $callback
     * @return self
     */
    public function call($callback)
    {
        if (!is_callable($callback))
        {
            throw new Exception('Invalid query callback given.');
        }

        call_user_func_array($callback, array(&$this));

        return $this;
    }

    /**
     * Creates a new raw db expression instance
     * 
     * @param string                $expression
     * @return Expression
     */
    final public function raw($expression)
    {
        return new Expression($expression);
    } 

    /**
     * Returns all avaialbe attribute data 
     * The result fetcher callback is excluded
     * 
     * @return array
     */
    final public function attributes()
    {
        $excluded = array('resultFetcher', 'macros');
        $attributes = get_object_vars($this);

        foreach ($excluded as $key) 
        {
            if (isset($attributes[$key])) { unset($attributes[$key]); }
        }

        return $attributes;
    }

    /**
     * Overwrite the query attributes
     * 
     * Jesuz only use this if you really really know what your are doing 
     * otherwise you might break stuff add sql injection and all other bad stuff..
     * 
     * @return array
     */
    final public function overwriteAttributes($attributes)
    {
        foreach($attributes as $key => $attribute)
        {
            if (isset($this->{$key}))
            {
                $this->{$key} = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Run the result fetcher and return the results
     *
     * @return mixed
     */
    final protected function executeResultFetcher()
    {
        if (is_null($this->resultFetcher))
        {
            throw new Exception('Cannot execute result fetcher callbacks without inital assignment.');
        }

        return call_user_func_array($this->resultFetcher, array(&$this));
    }

    /**
     * Public alias of executeResultFetcher
     * 
     * @return mixed
     */
    public function execute()
    {
        return $this->executeResultFetcher();
    }
}
