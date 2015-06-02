<?php namespace ClanCats\Hydrahon\Translator;

/**
 * Mysql query translator
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;
use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\TranslatorInterface;
use ClanCats\Hydrahon\Exception;

use ClanCats\Hydrahon\Query\Sql\Select;

class Mysql implements TranslatorInterface
{
    /**
     * The query parameters
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * The current query attributes
     * 
     * @param array
     */
    protected $attributes = array();

    /**
     * The escape pattern escapes table column names etc.
     * select * from `table`...
     *
     * @var string
     */
    protected $escape_pattern = '`%s`';

    /**
     * Translate the given query object and return the results as
     * argument array
     *
     * @param ClanCats\Hydrahon\BaseQuery                 $query
     * @return array
     */
    public function translate(BaseQuery $query)
    {
    	// retrive the query attributes
    	$this->attributes = $query->attributes();

    	// handle SQL SELECT queries
    	if ($query instanceof Select)
    	{
    		$queryString = $this->translateSelect();
    	}
    	// everything else is wrong
    	else
    	{
    		throw new Exception('Unknown query type. Cannot translate: '.get_class($query));
    	}

    	// get the query parameters and reset
    	$queryParameters = $this->parameters; $this->clearParameters();

    	return array($queryString, $queryParameters);
    }

    /**
     * Returns the an attribute value for the given key
     * 
     * @param string 				$key
     * @return mixed
     */
    protected function attr($key)
    {
    	return $this->attributes[$key];
    }

    /**
     * Check if the given argument is an sql expression
     *
     * @param mixed                 $expression
     * @return bool
     */
    protected function isExpression($expression)
    {
        return $expression instanceof Expression;
    }

    /**
     * Clear all set parameters
     *
     * @return void
     */
    protected function clearParameters()
    {
        $this->parameters = array();
    }

    /**
     * Adds a parameter to the builder
     *
     * @return void
     */
    protected function addParameter($value)
    {
        $this->parameters[] = $value;
    }

    /**
     * creates an parameter and adds it
     *
     * @param mixed         $value
     * @return string
     */
    protected function param($value)
    {
        if (!$this->isExpression($value)) 
        {
            $this->addParameter($value); return '?';
        }

        return $value;
    }

    /**
     * Filters the parameters removes the keys and Expressions
     *
     * @param array         $parameters
     * @return array
     */
    protected function filterParameters($parameters)
    {
        return array_values(array_filter($parameters, function ($item) 
        {
            return !$this->isExpression($item);
        }));
    }

    /**
     * Escape / wrap an string for sql
     *
     * @param string|Expression        $string
     */
    protected function escape($string)
    {
        if ($this->isExpression($string)) 
        {
            return $string->value();
        }

        // the string might contain an 'as' statement that we wil have to split.
        if (strpos($string, ' as ') !== false) 
        {
            $string = explode(' as ', $string);

            return $this->escape(trim($string[0])) . ' as ' . $this->escape(trim($string[1]));
        }

        // it also might contain dott seperations we have to split
        if (strpos($string, '.') !== false) 
        {
            $string = explode('.', $string);

            foreach ($string as $key => $item) 
            {
                $string[$key] = $this->escapeString($item);
            }

            return implode('.', $string);
        }

        return $this->escapeString($string);
    }

    /**
     * Escape a single string without checking for as and dots
     *
     * @param string     $string
     * @return string
     */
    protected function escapeString($string)
    {
        return sprintf($this->escape_pattern, $string);
    }

    /**
     * Escape an array of items an seprate them with a comma
     *
     * @param array         $array
     * @return string
     */
    protected function escapeList($array)
    {
        foreach ($array as $key => $item) 
        {
            $array[$key] = $this->escape($item);
        }

        return implode(', ', $array);
    }

    /**
     * get and escape the table name
     *
     * @return string
     */
    protected function escapeTable()
    {
        $table = $this->attr('table');
        $database = $this->attr('database');

        $buffer = '';

        if (!is_null($database))
        {
        	$buffer .= $this->escape($database) . '.';
        }

        if (is_array($table)) 
        {
            reset($table); $table = key($table) . ' as ' . $table[key($table)];
        }

        return $buffer . $this->escape($table);    
  	}

    /**
     * Convert data to parameters and bind them to the query
     *
     * @param array         $params
     * @return string
     */
    protected function parameterize($params)
    {
        foreach ($params as $key => $param) 
        {
            $params[$key] = $this->param($param);
        }

        return implode(', ', $params);
    }

    /**
     * Build an insert query
     *
     * @param Query     $query
     * @return string
     */
    protected function translateInsert(&$query)
    {
        $build = ($query->ignore ? 'insert ignore' : 'insert') . ' into ' . $this->escapeTable($query) . ' ';

        $value_collection = $query->values;

        // Get the array keys from the first array in the collection.
        // We use them as insert keys.
        $build .= '(' . $this->escapeList(array_keys(reset($value_collection))) . ') values ';

        // add the array values.
        foreach ($value_collection as $values) {
            $build .= '(' . $this->parameterize($values) . '), ';
        }

        // cut the last comma away
        return substr($build, 0, -2);
    }

    /**
     * Build an update query
     *
     * @param Query     $query
     * @return string
     */
    protected function translateUpdate(&$query)
    {
        $build = 'update ' . $this->escapeTable($query) . ' set ';

        // add the array values.
        foreach ($query->values as $key => $value) {
            $build .= $this->escape($key) . ' = ' . $this->param($value) . ', ';
        }

        $build = substr($build, 0, -2);
        $build .= $this->translateWhere($query);
        $build .= $this->translateLimit($query);

        // cut the last comma away
        return $build;
    }

    /**
     * Build an delete query
     *
     * @param Query     $query
     * @return string
     */
    protected function translateDelete(&$query)
    {
        $build = 'delete from ' . $this->escapeTable($query);

        $build .= $this->translateWhere($query);
        $build .= $this->translateLimit($query);

        // cut the last comma away
        return $build;
    }

    /**
     * Build a select
     *
     * @param Query     $query
     * @return string
     */
    protected function translateSelect()
    {
    	// normal or distinct selection?
        $build = ($this->attr('distinct') ? 'select distinct' : 'select') . ' ';

        // build the selected fields 
        $fields = $this->attr('fields');

        if (!empty($fields)) 
        {
            foreach ($fields as $key => $field) 
            {
            	list($column, $alias) = $field;

                if (!is_null($alias)) 
                {
                    $build .= $this->escape($column) . ' as ' . $this->escape($alias);
                }
                else 
                {
                    $build .= $this->escape($column);
                }

                $build .= ', ';
            }

            $build = substr($build, 0, -2);
        } 
        else 
        {
            $build .= '*';
        }

        // append the table
        $build .= ' from ' . $this->escapeTable();

        // build the join statements
        if ($this->attr('joins'))
        {
        	$build .= $this->translateJoins();
        }

        // build the where statements
        if ($wheres = $this->attr('wheres'))
        {
        	$build .= $this->translateWhere($wheres);
        }

        // build the groups
        if ($this->attr('groups'))
        {
        	$build .= $this->translateGroupBy();
        }

        // build the order statement
        if ($this->attr('orders'))
        {
        	$build .= $this->translateOrderBy();
        }
    
        // build offset and limit
        if ($this->attr('limit') || $this->attr('offset'))
        {
        	 $build .= $this->translateLimitWithOffset();
        }

        return $build;
    }

    /**
     * Translate the where statements into sql 
     * 
     * @param array 				$wheres
     * @return string
     */
    protected function translateWhere($wheres)
    {
        $build = '';

        foreach ($wheres as $where) 
        {
            // to make nested wheres possible you can pass an closure
            // wich will create a new query where you can add your nested wheres
            if (!isset($where[2]) && isset( $where[1] ) && $where[1] instanceof BaseQuery ) 
            {
            	$subAttributes = $where[1]->attributes();

                // The parameters get added by the call of compile where
                $build .= ' ' . $where[0] . ' ( ' . substr($this->translateWhere($subAttributes['wheres']), 7) . ' )';

                continue;
            }

            // when we have an array as where values we have
            // to parameterize them
            if (is_array($where[3])) 
            {
                $where[3] = '(' . $this->parameterize($where[3]) . ')';
            } else {
                $where[3] = $this->param($where[3]);
            }

            // we always need to escepe where 1 wich referrs to the key
            $where[1] = $this->escape($where[1]);

            // implode the beauty
            $build .= ' ' . implode(' ', $where);
        }

        return $build;
    }

    /**
     * Build the sql join statements
     *
     * @return string
     */
    protected function translateJoins()
    {
        $build = '';

        foreach ($this->attr('joins') as $join) 
        {
        	list($type, $table, $localKey, $operator, $referenceKey) = $join;
        	$build .= ' ' . $type . ' join ' . $this->escape($table) . ' on ' . $this->escape($localKey) . ' ' . $operator . ' ' . $this->escape($referenceKey);
        }

        return $build;
    }

    /**
     * Build the order by statement
     *
     * @return string
     */
    protected function translateOrderBy()
    {
        $build = " order by ";

        foreach ($this->attr('orders') as $column => $direction) 
        {
            $build .= $this->escape($column) . ' ' . $direction . ', ';
        }

        return substr($build, 0, -2);
    }

    /**
     * Build the gorup by statemnet
     *
     * @return string
     */
    protected function translateGroupBy()
    {
        return ' group by ' . $this->escapeList($this->attr('groups'));
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return string
     */
    protected function translateLimitWithOffset()
    {
        return ' limit ' . ((int) ($this->attr('offset'))) . ', ' . ((int) ($this->attr('limit')));
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return string
     */
    protected function translateLimit()
    {
        return ' limit ' . ((int) $this->attr('limit'));
    }
}
