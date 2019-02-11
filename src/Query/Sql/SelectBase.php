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

use ClanCats\Hydrahon\Query\Sql\Keyword\{ConditionBinOp,BinOp,SpecialValue};

class SelectBase extends Base
{ 
    /**
     * The query where statements
     *
     * @var array
     */
    protected $wheres = [];

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
     * Function to check if sub queries have been generated correctly, to avoid translation errors
     *
     * @return bool
     */
    protected function isValid(): bool
    {
        return !empty($this->wheres);
    }

    /**
     * Returns an string argument as parsed array if possible
     * 
     * @param string                $argument
     * @return array
     */
    protected function stringArgumentToArray(string $argument): array
    {
        if ( strpos($argument, ',') !== false )
        {
            return array_map('trim', explode(',', $argument));
        }

        return [$argument];
    }

    /**
     * Will reset the current selects where conditions
     * 
     * @return self The current query builder.
     */
    public function resetWheres(): self
    {
        $this->wheres = [];
        return $this;
    }

    /**
     * Will reset the current selects limit
     * 
     * @return self The current query builder.
     */
    public function resetLimit(): self
    {
        $this->limit = null;
        return $this;
    }

    /**
     * Will reset the current selects offset
     * 
     * @return self The current query builder.
     */
    public function resetOffset()
    {
        $this->offset = null;
        return $this;
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
    public function where($column, $param1 = null, $param2 = null, string $type = 'and'): self
    {
        // if this is the first where element we are going to change
        // the where type to 'where'
        if (!empty($this->wheres)) {
            if ($type === 'where') {
                $type = 'and';
            }
            // check if the where type is valid
            $wheretype = new ConditionBinOp($type);
        } else {
            $wheretype = null;
        }

        // when column is an array we assume to make a bulk and where.
        if (is_array($column))
        {
            $subquery = new SelectBase;
            foreach ($column as $key => $val) 
            {
                $subquery->where($key, $val, null, $type);
            }

            $this->wheres[] = [$wheretype, $subquery];
            return $this;
        }

        // to make nested wheres possible you can pass an closure
        // which will create a new query where you can add your nested wheres
        if (is_object($column) && ($column instanceof \Closure)) {
            $subquery = $this->generateSubQuery($column, new SelectBase);

            $this->wheres[] = [$wheretype, $subquery];
            return $this;
        }

        // when param2 is null we replace param2 with param one as the
        // value holder and make param1 to the = operator.
        if (is_null($param2))
        {
            $param2 = $param1;
            $param1 = '=';
        }

        $operator = new BinOp($param1);

        // if the param2 is an array we filter it. Im no more sure why
        // but it's there since 4 years so i think i had a reason.
        // edit: Found it out, when param2 is an array we probably 
        // have an "in" or "between" statement which has no need for dublicates.
        if (is_array($param2)) 
        {
            $param2 = array_unique($param2);
        }

        $this->wheres[] = [$wheretype, $column, $operator, $param2];
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
     * @return self The current query builder.
     */
    public function orWhere($column, $param1 = null, $param2 = null): self
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
    public function andWhere($column, $param1 = null, $param2 = null): self
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
    public function whereIn($column, array $options = []): self
    {
        // when the options are empty we skip
        if (empty($options))
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
    public function whereNull($column): self
    {
        return $this->where($column, 'is', new SpecialValue('NULL'));
    }

     /**
     * Creates a where something is not null statement
     * 
     *     ->whereNotNull('created_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function whereNotNull($column): self
    {
        return $this->where($column, 'is not', new SpecialValue('NULL'));
    }

    /**
     * Creates a or where something is null statement
     * 
     *     ->orWhereNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orWhereNull($column): self
    {
        return $this->orWhere($column, 'is', new SpecialValue('NULL'));
    }

    /**
     * Creates a or where something is not null statement
     * 
     *     ->orWhereNotNull('modified_at')
     * 
     * @param string                    $column
     * @return self The current query builder.
     */
    public function orNotNull($column): self
    {
        return $this->orWhere($column, 'is not', new SpecialValue('NULL'));
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
    public function limit(?int $limit, ?int $limit2 = null): self
    {
        if (!is_null($limit2)) 
        {
            $this->offset = $limit;
            $this->limit = $limit2;
        } else {
            $this->limit = $limit;
        }

        return $this;
    }

    /**
     * Set the queries current offset
     * 
     * @param int               $offset
     * @return self The current query builder.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Create an query limit based on a page and a page size
     *
     * @param int        $page
     * @param int         $size
     * @return self The current query builder.
     */
    public function page(int $page, int $size = 25): self
    {
        if ($page < 0)
        {
            $page = 0;
        }

        $this->limit = $size;
        $this->offset = $size * $page;

        return $this;
    }
}
