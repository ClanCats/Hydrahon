<?php namespace ClanCats\Hydrahon\Query;

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
     * Create a new select query builder
     * 
     *     $h->table('users')->select(['name', 'age'])
     *
     * @param string|array                              $fields
     * @return ClanCats\Hydrahon\Query\Sql\Select
     */
    public function select($fields = null)
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Select')->fields($fields);
    }

    /**
     * Create a new insert query builder
     * 
     *     $h->table('users')->insert(['name' => 'Lucas', 'age' => 21])
     *
     * @param array                                     $values
     * @return ClanCats\Hydrahon\Query\Sql\Insert
     */
    public function insert(array $values = array())
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Insert')->values($values);
    }

    /**
     * Create a new update query builder
     *
     *     $h->table('users')->update(['age' => 25])->where('name', 'Johanna')
     *         
     * @param array                                  $values
     * @return ClanCats\Hydrahon\Query\Sql\Update
     */
    public function update(array $values = array())
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Update')->set($values);
    }

    /**
     * Create a new delete sql builder
     * 
     *     $h->table('users')->delete()->where('age', '<', '18')
     *
     * @return ClanCats\Hydrahon\Query\Sql\Delete
     */
    public function delete()
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Delete');
    }

    /**
     * Create a new drop table query
     * 
     *     $h->table('users')->drop()
     *
     * @return ClanCats\Hydrahon\Query\Sql\Drop
     */
    public function drop()
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Drop');
    }

    /**
     * Create a new truncate table query
     * 
     *     $h->table('users')->truncate()
     *
     * @return ClanCats\Hydrahon\Query\Sql\Truncate
     */
    public function truncate()
    {
        return $this->createSubQuery(__NAMESPACE__ . '\\Sql\\Truncate');
    }
}