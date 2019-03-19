<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\Query\Sql\Keyword\{ConditionBinOp,BinOp};

class SelectJoin extends SelectBase
{
    /**
     * join on items
     *
     * @var array
     */
    protected $ons = [];

    /**
     * The query where statements
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * Function to check if sub queries have been generated correctly, to avoid translation errors
     *
     * @return bool
     */
    protected function isValid(): bool
    {
        return !empty($this->ons);
    }

    /**
     * Add an on condition to the join object
     * 
     * @param string                $localKey
     * @param string                $operator
     * @param string                $referenceKey
     * 
     * @return self
     */
    public function on($localKey, string $operator, $referenceKey, string $type = 'and'): self
    {
        $ontype = new ConditionBinOp($type);
        $comparison = new BinOp($operator);

        $this->ons[] = [$ontype, $localKey, $comparison, $referenceKey];
        return $this;
    }

    /**
     * Add an or on condition to the join object
     * 
     * @param string                $localKey
     * @param string                $operator
     * @param string                $referenceKey
     * 
     * @return self
     */
    public function orOn($localKey, $operator, $referenceKey)
    {
        $this->on($localKey, $operator, $referenceKey, 'or');
        return $this;
    }

     /**
     * Add an and on condition to the join object
     * 
     * @param string                $localKey
     * @param string                $operator
     * @param string                $referenceKey
     * 
     * @return self
     */
    public function andOn($localKey, $operator, $referenceKey): self
    {
        $this->on($localKey, $operator, $referenceKey, 'and');
        return $this;
    }
}
