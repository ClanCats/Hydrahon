<?php 

namespace ClanCats\Hydrahon\Query\Sql;

/**
 * The table base
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class Table extends Base
{
    /**
     * Create a new select query builder
     * 
     *     $h->table('users')->select(['name', 'age'])
     *
     * @param string|array                              $fields
     * @return Select
     */
    public function select(...$fields): Select
    {
        $query = new Select($this);
        return $query->fields(count($fields)==1 ? $fields[key($fields)] : $fields);
    }

    /**
     * Create a new insert query builder
     * 
     *     $h->table('users')->insert(['name' => 'Lucas', 'age' => 21])
     *
     * @param array                                     $values
     * @return Insert
     */
    public function insert(array $values = []): Insert
    {
        $query = new Insert($this);
        return $query->values($values);
    }

    /**
     * Create a new replace query builder
     * 
     *     $h->table('users')->replace(['name' => 'Lucas', 'age' => 21])
     *
     * @param array                                     $values
     * @return Insert
     */
    public function replace(array $values = []): Replace
    {
        $query = new Replace($this); return $query->values($values);
    }

    /**
     * Create a new update query builder
     *
     *     $h->table('users')->update(['age' => 25])->where('name', 'Johanna')
     *         
     * @param array                                  $values
     * @return Update
     */
    public function update(array $values = []): Update
    {
        $query = new Update($this);
        return $query->set($values);
    }

    /**
     * Create a new delete sql builder
     * 
     *     $h->table('users')->delete()->where('age', '<', '18')
     *
     * @return Delete
     */
    public function delete(): Delete
    {
        return new Delete($this);
    }

    /**
     * Create a new drop table query
     * 
     *     $h->table('users')->drop()
     *
     * @return Drop
     */
    public function drop(): Drop
    {
        return new Drop($this);
    }

    /**
     * Create a new truncate table query
     * 
     *     $h->table('users')->truncate()
     *
     * @return Truncate
     */
    public function truncate(): Truncate
    {
        return new Truncate($this);
    }
}
