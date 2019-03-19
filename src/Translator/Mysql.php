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
use ClanCats\Hydrahon\Query\Sql\Insert;
use ClanCats\Hydrahon\Query\Sql\Replace;
use ClanCats\Hydrahon\Query\Sql\Update;
use ClanCats\Hydrahon\Query\Sql\Delete;
use ClanCats\Hydrahon\Query\Sql\Drop;
use ClanCats\Hydrahon\Query\Sql\Truncate;
use ClanCats\Hydrahon\Query\Sql\Func;
use ClanCats\Hydrahon\Query\Sql\Exists;
use ClanCats\Hydrahon\Query\Sql\Field;
use ClanCats\Hydrahon\Query\Sql\Keyword;
use ClanCats\Hydrahon\Query\Sql\Keyword\SpecialValue;

class Mysql implements TranslatorInterface
{
    /**
     * The query parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The current query attributes
     *
     * @param array
     */
    protected $attributes = [];

    /**
     * Translate the given query object and return the results as
     * argument array
     *
     * @param ClanCats\Hydrahon\BaseQuery                 $query
     * @return array
     */
    public function translate(BaseQuery $query): array
    {
        // retrive the query attributes
        $this->attributes = $query->attributes();

        // handle SQL SELECT queries
        if ($query instanceof Select)
        {
            $queryString = $this->translateSelect();
        }
        // handle SQL INSERT queries
        elseif ($query instanceof Replace)
        {
            $queryString = $this->translateInsert('replace');
        }
        // handle SQL INSERT queries
        elseif ($query instanceof Insert)
        {
            $queryString = $this->translateInsert('insert');
        }
        // handle SQL UPDATE queries
        elseif ($query instanceof Update)
        {
            $queryString = $this->translateUpdate();
        }
        // handle SQL UPDATE queries
        elseif ($query instanceof Delete)
        {
            $queryString = $this->translateDelete();
        }
        // handle SQL DROP queries
        elseif ($query instanceof Drop)
        {
            $queryString = $this->translateDrop();
        }
        // handle SQL TRUNCATE queries
        elseif ($query instanceof Truncate)
        {
            $queryString = $this->translateTruncate();
        }
        elseif ($query instanceof Exists)
        {
            $queryString = $this->translateExists();
        }
        // everything else is wrong
        else
        {
            throw new Exception('Unknown query type. Cannot translate: '.get_class($query));
        }

        // get the query parameters and reset
        $queryParameters = $this->parameters;
        $this->clearParameters();

        return [$queryString, $queryParameters];
    }

    /**
     * Returns the attribute value for the given key
     *
     * @param string                $key
     * @return mixed
     */
    protected function attr(string $key)
    {
        return $this->attributes[$key];
    }

    /**
     * Check if the given argument is an sql expression
     *
     * @param mixed                 $expression
     * @return bool
     */
    protected function isExpression($expression): bool
    {
        return $expression instanceof Expression;
    }

    /**
     * Check if the given argument is an sql function
     *
     * @param mixed                 $expression
     * @return bool
     */
    protected function isFunction($function): bool
    {
        return $function instanceof Func;
    }

    protected function isKeyword($keyword): bool
    {
        return $keyword instanceof Keyword;
    }

    protected function isSpecialValue($keyword): bool
    {
        return $keyword instanceof SpecialValue;
    }

    protected function isField($field): bool
    {
        return $field instanceof Field;
    }

    protected function isParam($value): bool
    {
        return !($value instanceof Expression || $value instanceof Keyword || $value instanceof Func || $value instanceof Field);
    }

    /**
     * Clear all set parameters
     *
     * @return void
     */
    protected function clearParameters(): void
    {
        $this->parameters = [];
    }

    /**
     * Adds a parameter to the builder
     *
     * @return void
     */
    protected function addParameter($value): void
    {
        $this->parameters[] = $value;
    }

    /**
     * creates an parameter and adds it
     *
     * @param mixed         $value
     * @return string
     */
    protected function param($value): string
    {
        $this->addParameter($value);
        return '?';
    }

    /**
     * Translate a parameter
     *
     * @return string
     */
    protected function translateParam($param): string 
    {
        if ($this->isParam($param))    {
            return $this->param($param);
        } else if ($this->isField($param)) {
            return $this->escape($param);
        }

        return $param;
    }

    /**
     * Filters the parameters removes the keys and Expressions
     *
     * @param array         $parameters
     * @return array
     */
    protected function filterParameters(array $parameters)
    {
        return array_values(array_filter($parameters, function ($item): bool
        {
            return !$this->isExpression($item);
        }));
    }

    /**
     * Function to escape identifier names (columns and tables)
     * Doubles backticks, removes null bytes
     * https://dev.mysql.com/doc/refman/8.0/en/identifiers.html
     *
     * @var string
     */
    public function escapeIdentifier(string $identifier): string
    {
        return '`'.str_replace(['`',"\0"],['``',''],$identifier).'`';
    }

    /**
     * Escape / wrap a string for sql
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
            elseif ($this->isKeyword($string))
            {
                return (string)$string;
            }
            else if ($this->isField($string))
            {
                $string = (string)$string;
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
                $string[$key] = $this->escapeIdentifier($item);
            }

            return implode('.', $string);
        }

        return $this->escapeIdentifier($string);
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
     * Escape an array of items an seprate them with a comma
     *
     * @param array         $array
     * @return string
     */
    protected function escapeList(array $array): string
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
    protected function translateTable(bool $allowAlias = true): string
    {
        $buffer = '';

        $table = $this->attr('table');
        $database = $this->attr('database');

        $dbname = is_null($database)? '' : $this->escape($database).'.';

        // when the table is an array we have a table with alias
        if (is_array($table) && !empty($table)) {
            reset($table);

            foreach ($table as $tableref=>$alias) {
                // the table might be a subselect so check that, first and compile the select if it is one
                // notice that alias and table reference are inverted if using a subselect
                if ($alias instanceof Select) {
                    $subQuery = $this->translateSubQuery($alias);

                    $buffer .= '(' . $subQuery . ') as ' . $this->escape($tableref);
                } else {
                    // otherwise continue with normal table
                    $buffer .= $dbname . $this->escape($tableref);

                    if ($allowAlias) {
                        $buffer .= ' as ' . $this->escape($alias);
                    }
                }

                $buffer .= ',';
            }

            $buffer = rtrim($buffer,',');
        } else {
            $buffer .= $dbname . $this->escape($table);
        }

        return $buffer;
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
            $params[$key] = $this->translateParam($param);
        }

        return implode(', ', $params);
    }

    /*
     * -- FROM HERE TRANSLATE FUNCTIONS FOLLOW
     */

    /**
     * Translate the current query to an SQL insert statement
     *
     * @return string
     */
    protected function translateInsert($key): string
    {
        $build = ($this->attr('ignore') ? $key . ' ignore' : $key);

        $build .= ' into ' . $this->translateTable(false) . ' ';

        if (!$valueCollection = $this->attr('values'))
        {
            throw new Exception('Cannot build insert query without values.');
        }

        // Get the array keys from the first array in the collection.
        // We use them as insert keys. If you pass an array collection with
        // missing keys or a diffrent structure well... f*ck
        $build .= '(' . $this->escapeList(array_keys(reset($valueCollection))) . ') values ';

        // add the array values.
        foreach ($valueCollection as $values) 
        {
            $build .= '(' . $this->parameterize($values) . '), ';
        }

        // cut the last comma away
        return substr($build, 0, -2);
    }

    /**
     * Translate the current query to an SQL update statement
     *
     * @return string
     */
    protected function translateUpdate(): string
    {
        $build = 'update ' . $this->translateTable() . ' set ';

        // add the array values.
        foreach ($this->attr('values') as $key => $value) {
            $build .= $this->escape($key) . ' = ' . $this->translateParam($value) . ', ';
        }

        // cut away the last comma and space
        $build = substr($build, 0, -2);

        // build the where statements
        if ($wheres = $this->attr('wheres'))
        {
            $build .= $this->translateWhere($wheres);
        }

        // build offset and limit
        if ($this->attr('limit'))
        {
             $build .= $this->translateLimit();
        }

        return $build;
    }

    /**
     * Translate the current query to an SQL delete statement
     *
     * @return string
     */
    protected function translateDelete(): string
    {
        $build = 'delete from ' . $this->translateTable(false);

        // build the where statements
        if ($wheres = $this->attr('wheres'))
        {
            $build .= $this->translateWhere($wheres);
        }

        // build offset and limit
        if ($this->attr('limit'))
        {
             $build .= $this->translateLimit();
        }

        return $build;
    }

    /**
     * Translate the current query to an SQL select statement
     *
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
                [$column, $alias] = $field;

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
        $build .= ' from ' . $this->translateTable();

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

        // build the unions
        if ($unions = $this->attr('unions'))
        {
            $build .= $this->translateUnions($unions);
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
     * @param array                 $wheres
     * @return string
     */
    protected function translateWhere(array $wheres): string
    {
        $build = ' where';

        foreach ($wheres as $where)
        {
            // to make nested wheres possible you can pass an closure
            // wich will create a new query where you can add your nested wheres
            if (!isset($where[2]) && isset($where[1]) && $where[1] instanceof BaseQuery)
            {
                $subAttributes = $where[1]->attributes();

                // The parameters get added by the call of compile where
                if (!is_null($where[0])) {
                	$build .= ' ' . $where[0];
                }
                $build .= ' ( ' . substr($this->translateWhere($subAttributes['wheres']), 7) . ' )';

                continue;
            }

            // when we have an array as where values we have
            // to parameterize them
            if (is_array($where[3])) 
            {
                $where[3] = '(' . $this->parameterize($where[3]) . ')';
            } else {
                $where[3] = $this->translateParam($where[3]);
            }

            // we always need to escape where[1], which refers to the key
            $where[1] = $this->escape($where[1]);

            // first where has no where type
            if (is_null($where[0])) {
                unset($where[0]);
            }

            // implode the beauty
            $build .= ' ' . implode(' ', $where);
        }

        return $build;
    }

    /**
     * Translate the unions into sub selects
     *
     * @param array                 $unions
     * @return string
     */
    protected function translateUnions($unions): string
    {
        $build = '';
        foreach ($unions as $union) {
            $build .= ' union '.(!is_null($union[0])? $union[0].' ' : '');
            $build .= '('.$this->translateSubQuery($union[1]).')';
        }
        return $build;
    }

    /**
     * Translate the subquery
     *
     * @param array                 $select
     * @return string
     */
    protected function translateSubQuery($select): string
    {
        $translator = new static;

        // translate the subselect
        [$subQuery, $subQueryParameters] = $translator->translate($select);

        // merge the parameters
        foreach($subQueryParameters as $parameter)
        {
            $this->addParameter($parameter);
        }

        return $subQuery;
    }

    /**
     * Build the sql join statements
     *
     * @return string
     */
    protected function translateJoins(): string
    {
        $build = '';

        foreach ($this->attr('joins') as $join)
        {
            // get the type and table
            $type = $join[0];
            $table = $join[1];

            $tablequery = '';

            // table
            if (is_array($table))
            {
                reset($table);

                // the table might be a subselect so check that
                // first and compile the select if it is one
                if ($table[key($table)] instanceof Select)
                {
                    $subQuery = $this->translateSubQuery($table[key($table)]);
                    $tablequery = '(' . $subQuery . ') as ' . $this->escape(key($table));
                }
            } else {
                $tablequery = $this->escape($table);
            }

            // start the join
            $build .= ' ' . $type . ' join ' . $tablequery . ' on ';

            // to make nested join conditions possible you can pass an closure
            // wich will create a new query where you can add your nested ons and wheres
            if (!isset($join[3]) && isset($join[2]) && $join[2] instanceof BaseQuery)
            {
                $subAttributes = $join[2]->attributes();

                $joinConditions = '';

                // remove the first type from the ons
                reset($subAttributes['ons']);
                $subAttributes['ons'][key($subAttributes['ons'])][0] = '';

                foreach($subAttributes['ons'] as $on)
                {
                    list($type, $localKey, $operator, $referenceKey) = $on;
                    $joinConditions .= ' ' . $type . ' ' . $this->escape($localKey) . ' ' . $operator . ' ' . $this->escape($referenceKey);
                }

                $build .= trim($joinConditions);

                // compile the where if set
                if (!empty($subAttributes['wheres']))
                {
                    $build .= ' and ' . substr($this->translateWhere($subAttributes['wheres']), 7);
                }
            }
            else
            {
                // othewise default join
                [$type, $table, $localKey, $operator, $referenceKey] = $join;
                $build .= $this->escape($localKey) . ' ' . $operator . ' ' . $this->escape($referenceKey);
            }
        }

        return $build;
    }

    /**
     * Build the order by statement
     *
     * @return string
     */
    protected function translateOrderBy(): string
    {
        $build = ' order by ';

        foreach ($this->attr('orders') as $column => $direction)
        {
            // in case a raw value is given we had to
            // put the column / raw value an direction inside another
            // array because we cannot make objects to array keys.
            if (is_array($direction))
            {
                // This only works in php 7 the php 5 fix is below 
                [$column, $direction] = $direction;
                //$column = $direction[0];
                //$direction = $direction[1];
            }

            $build .= $this->escape($column) . ' ' . $direction . ', ';
        }

        return substr($build, 0, -2);
    }

    /**
     * Build the gorup by statemnet
     *
     * @return string
     */
    protected function translateGroupBy(): string
    {
        return ' group by ' . $this->escapeList($this->attr('groups'));
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return string
     */
    protected function translateLimitWithOffset(): string
    {
        return ' limit ' . ((int) ($this->attr('offset'))) . ', ' . ((int) ($this->attr('limit')));
    }

    /**
     * Build the limit and offset part
     *
     * @param Query         $query
     * @return string
     */
    protected function translateLimit(): string
    {
        return ' limit ' . ((int) $this->attr('limit'));
    }

    /**
     * Translate the current query to an sql DROP statement
     *
     * @return string
     */
    protected function translateDrop(): string
    {
        return 'drop table ' . $this->translateTable() .';';
    }

    /**
     * Translate the current query to an sql DROP statement
     *
     * @return string
     */
    protected function translateTruncate(): string
    {
        return 'truncate table ' . $this->translateTable() .';';
    }

    /**
     * Translate the exists querry
     *
     * @return string
     */
    protected function translateExists(): string
    {
        $subQuery = $this->translateSubQuery($this->attr('select'));
        return 'select exists(' . $subQuery .') as `exists`';
    }
}
