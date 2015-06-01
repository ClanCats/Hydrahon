<?php namespace ClanCats\Hydrahon;

/**
 * SQL query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

class Sql extends BaseQuery
{
    /**
     * Create a new select sql query
     *
     * @param string|array                              $fields
     * @return ClanCats\Hydrahon\Query\Sql\Select
     */
    public function select($fields = null)
    {
        return $this->createSubQuery('ClanCats\\Hydrahon\\Query\\Sql\\Select')->fields($fields);
    }

    /**
     * Create a new insert sql query
     *
     * @param array                                     $data
     * @return ClanCats\Hydrahon\Query\Sql\Insert
     */
    public function insert($data = null)
    {
        return $this->createSubQuery('ClanCats\\Hydrahon\\Query\\Sql\\Insert')->insert($data);
    }

    /**
     * Create a new update sql query
     *
     * @param string|array                              $fields
     * @return ClanCats\Hydrahon\Query\Sql\Update
     */
    public function update($values = null)
    {
        return $this->createSubQuery('ClanCats\\Hydrahon\\Query\\Sql\\Update')->values($values);
    }

    /**
     * Create a new select sql query
     *
     * @param string|array                              $fields+
     * @return ClanCats\Hydrahon\Query\Sql\Delete
     */
    public function delete($fields = null)
    {
        return $this->createSubQuery('ClanCats\\Hydrahon\\Query\\Sql\\Delete')->select($fields);
    }
}