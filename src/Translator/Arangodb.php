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

use ClanCats\Hydrahon\Query\Aql;

class Arangodb implements TranslatorInterface
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
    protected $escapePattern = '`%s`';

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

        // start the loops
        $queryString = $this->translateFor();

        // translate the filters
        $queryString = $this->translateFilter();

        // build limit and offset
        $queryString .= $this->translateLimitWithOffset();

        // translate subuery
        if ($this->attr('subquery') && $this->attr('subquery') instanceof Aql)
        {
            $queryString .= $this->translateSubquery($this->attr('subquery'));
        }

        $queryString .= $this->translateReturn();

        // get the query parameters and reset
        $queryParameters = $this->parameters; $this->clearParameters();

        return array($queryString, $queryParameters);
    }

    /**
     * Returns the an attribute value for the given key
     * 
     * @param string                $key
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
     * Check if the given argument is an sql function
     *
     * @param mixed                 $expression
     * @return bool
     */
    protected function isFunction($function)
    {
        return $function instanceof Func;
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
     * @param string|object                 $string
     */
    protected function escape($string)
    {
        if (is_object($string))
        {
            if ($this->isExpression($string)) 
            {
                return $string->value();
            }
            elseif ($this->isFunction($string))
            {
                return $this->escapeFunction($string);
            }
            else
            {
                throw new Exception('Cannot translate object of class: ' . get_class($string));
            }
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
     * Escapes an sql function object
     * 
     * @param Func              $function
     * @return string
     */
    protected function escapeFunction($function)
    {
        $buffer = $function->name() . '(';

        $arguments = $function->arguments();

        foreach($arguments as &$argument)
        {
            $argument = $this->escape($argument);
        }

        return $buffer . implode(', ', $arguments) . ')';
    }

    /**
     * Escape a single string without checking for as and dots
     *
     * @param string     $string
     * @return string
     */
    protected function escapeString($string)
    {
        return sprintf($this->escapePattern, $string);
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

    /*
     * -- FROM HERE TRANSLATE FUNCTIONS FOLLOW
     */

    /**
     * Translate a subquery object with the given callback
     * 
     * @param callable
     * @return string
     */
    protected function translateSubquery($callback)
    {
        $translator = new static;

        list($subQuery, $subQueryParameters) = $translator->translate($this->attr('subquery'));

        // merge the parameters
        foreach($subQueryParameters as $parameter)
        {
            $this->addParameter($parameter);
        }

        return $subQuery;
    }

    /**
     * Translate the current query to an SQL select statement
     *
     * @return string
     */
    protected function translateFor()
    {
        if ($this->attr('for') && $this->attr('in'))
        {
            return  'FOR ' . $this->escape($this->attr('for')) . ' IN ' . $this->escape($this->attr('in'));
        }

        return '';
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return stringlimit
     */
    protected function translateLimitWithOffset()
    {
        if ($this->attr('limit') || $this->attr('offset'))
        {
            return ' LIMIT ' . ((int) ($this->attr('offset'))) . ', ' . ((int) ($this->attr('limit')));
        }

        return '';        
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return stringlimit
     */
    protected function translateReturn()
    {
        if ($return = $this->attr('return'))
        {
            return ' RETURN ' . $this->escape($return);
        }

        return '';        
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return stringlimit
     */
    protected function translateFilter()
    {
        $build = 'FILTER ';

        foreach ($this->attr('filters') as $filterKey => $filter) 
        {
            // ad the filter type if not the first filter
            if ($filterKey !== 0) 
            {
                $filterType = reset($filter);

                if ($filterType === 'and') {
                    $build .= '&& ';
                }  elseif ($filterType === 'or') {
                    $build .= '|| ';
                }
            }

            // to make nested filters possible you can pass a closure
            // wich will create a new query where you can add your nested wheres
            if (!isset($filter[3]) && isset($filter[2]) && $filter[2] instanceof Aql) 
            {
                // The parameters get added by the call of compile where
                $build .= ' (' . $this->translateSubquery($filter[2]) . ')'; continue;
            }

            var_dump($filter); die;
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
}
