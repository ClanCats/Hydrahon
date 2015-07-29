<?php namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\Query\Expression;

class Select extends BaseSql
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
    protected $groupResult = false;

    /**
     * Forward a value as key
     *
     * @var false|string
     */
    protected $forwardKey = false;

    /**
     * Distinct select setter
     *
     * @param bool        $ignore
     * @return self
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
     * @return self
     */
    public function fields($fields)
    {
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
        if (empty($fields) || $fields === array('*') || $fields === array('')) 
        {
            $this->fields = array(); return $this;
        }

        $this->fields = array();

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
     * @param string         		$field
     * @param string 				$alias
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @param array|string     			$cols
     * @param string    				$order
     * @return self
     */
    public function orderBy($columns, $direction = 'asc')
    {
    	if (is_string($columns))
    	{
    		$columns = $this->stringArgumentToArray($columns);
    	}

        foreach ($columns as $key => $column) 
        {
            if (is_numeric($key)) 
            {
                $this->orders[$column] = $direction;
            } else {
                $this->orders[$key] = $column;
            }
        }

        return $this;
    }

    /**
     * Add a group by statement to the current query
     * 
     *     ->groupBy('id')
     *     ->gorupBy(['id', 'category'])
     *
     * @param array|string     			$keys
     * @return self
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
     * Add a join statement to the current query
     * 
     *     ->join('avatars', 'users.id', '=', 'avatars.user_id')
     *
     * @param array|string     			$table
     * @param string 					$localKey
     * @param string 					$operator
     * @param string 					$referenceKey
     * @param string 					$type
     * 
     * @return self
     */
    public function join($table, $localKey, $operator, $referenceKey, $type = 'left')
    {
    	// validate the join type
    	if (!in_array($type, array('inner', 'left', 'right', 'outer')))
    	{
    		throw new Exception('Invalid join type "'.$type.'" given. Available type: inner, left, right, outer');
    	}

    	$this->joins[] = array($type, $table, $localKey, $operator, $referenceKey); return $this;
    }

    /**
     * Left join same as join with special type
     *
     * @param array|string     			$table
     * @param string 					$localKey
     * @param string 					$operator
     * @param string 					$referenceKey
     * 
     * @return self
     */
    public function leftJoin($table, $localKey, $operator, $referenceKey)
    {
    	return $this->join($table, $localKey, $operator, $referenceKey, 'left');
    }

    /**
     * Left join same as join with special type
     *
     * @param array|string     			$table
     * @param string 					$localKey
     * @param string 					$operator
     * @param string 					$referenceKey
     * 
     * @return self
     */
    public function rightJoin($table, $localKey, $operator, $referenceKey)
    {
    	return $this->join($table, $localKey, $operator, $referenceKey, 'right');
    }

    /**
     * Left join same as join with special type
     *
     * @param array|string     			$table
     * @param string 					$localKey
     * @param string 					$operator
     * @param string 					$referenceKey
     * 
     * @return self
     */
    public function innerJoin($table, $localKey, $operator, $referenceKey)
    {
    	return $this->join($table, $localKey, $operator, $referenceKey, 'inner');
    }

    /**
     * Left join same as join with special type
     *
     * @param array|string     			$table
     * @param string 					$localKey
     * @param string 					$operator
     * @param string 					$referenceKey
     * 
     * @return self
     */
    public function outerJoin($table, $localKey, $operator, $referenceKey)
    {
    	return $this->join($table, $localKey, $operator, $referenceKey, 'outer');
    }

    /**
     * Forward a result value as array key
     *
     * @param string|bool        $key
     * @return self
     */
    public function forward_key($key = true)
    {
        if ($key === false) {
            $this->forward_key = false;
        } elseif ($key === true) {
            $this->forward_key = \ClanCats::$config->get('database.default_primary_key', 'id');
        } else {
            $this->forward_key = $key;
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
     * @return self
     */
    public function group_result($key)
    {
        if ($key === false) {
            $this->group_result = false;
        } else {
            $this->group_result = $key;
        }

        return $this;
    }

    /**
     * Executes the 'executeResultFetcher' callback and handles the results
     *
     * @param string         $handler
     * @return mixed
     */
    public function run()
    {
        // run the callbacks to retirve the results
        $results = $this->executeResultFetcher();

        // we always exprect an array here!
        if (!is_array($results))
        {
        	$results = array();
        }

        // In case we should forward a key means using a value
        // from every result as array key.
        // if ($this->forward_key !== false) {
        //     $raw_results = $results;
        //     $results = array();

        //     if (in_array('assoc', $this->fetch_arguments)) {
        //         foreach ($raw_results as $result) {
        //             $results[$result[$this->forward_key]] = $result;
        //         }
        //     } else {
        //         foreach ($raw_results as $result) {
        //             $results[$result->{$this->forward_key}] = $result;
        //         }
        //     }
        // }

        // // Group the results by a value
        // if ($this->group_result !== false) {
        //     $raw_results = $results;
        //     $results = array();

        //     if (in_array('assoc', $this->fetch_arguments)) {
        //         foreach ($raw_results as $key => $result) {
        //             $results[$result[$this->group_result]][$key] = $result;
        //         }
        //     } else {
        //         foreach ($raw_results as $key => $result) {
        //             $results[$result->{$this->group_result}][$key] = $result;
        //         }
        //     }
        // }

        // when the limit is specified to exactly one result we
        // return directly that one result instead of the entire array
        if ($this->limit === 1) 
        {
            $results = reset($results);
        }

        return $results;
    }

    /**
     * Get one result sets limit to 1 and executes
     *
     * @return mixed
     */
    public function one()
    {
        return $this->limit(0, 1)->run();
    }

    /**
     * Find something, means select one record by key
     *
     * @param int           	$id
     * @param string            $key
     * @return mixed
     */
    public function find($id, $key = 'id')
    {
        return $this->where($key, $id)->one();
    }

    /**
     * Get the first result by key
     *
     * @param string            $key
     * @return mixed
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
     * @return mixed
     */
    public function last($key = 'id')
    {
        return $this->orderBy($key, 'desc')->one();
    }

    /**
     * Just get a single value from the db
     *
     * @param string            $column
     * @return mixed
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
     * @return int
     */
    public function count()
    {
        return (int) $this->column(array($this->func('count', '*')));
    }
}
