<?php namespace ClanCats\Hydrahon\Query;

/**
 * SQL query object
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;

use ClanCats\Hydrahon\Query\Sql\Select;
use ClanCats\Hydrahon\Query\Sql\Insert;
use ClanCats\Hydrahon\Query\Sql\Update;
use ClanCats\Hydrahon\Query\Sql\Delete;
use ClanCats\Hydrahon\Query\Sql\Drop;
use ClanCats\Hydrahon\Query\Sql\Truncate;
use ClanCats\Hydrahon\Query\Sql\Table;
use ClanCats\Hydrahon\Query\Sql\Field;
use ClanCats\Hydrahon\Query\Sql\Keyword\SpecialValue;

class Sql extends BaseQuery
{
    /**
     * Create a new table instance
     *
     *     $h->table('users')
     *
     * @param string|array                              $fields
     * @return Table
     */
    public function table($table = null, ?string $alias = null): Table
    {
        $query = new Table($this);
        return $query->table($table, $alias);
    }

    /**
     * Create a new select query builder
     *
     *     $h->select('users', ['name', 'age'])
     *
     * @param string|array                              $fields
     * @return Select
     */
    public function select($table = null, ...$fields): Select
    {
        return $this->table($table)->select(...$fields);
    }

    /**
     * Create a new insert query builder
     *
     *     $h->insert('users', ['name' => 'Lucas', 'age' => 21])
     *
     * @param array                                     $values
     * @return Insert
     */
    public function insert($table = null, array $values = []): Insert
    {
        return $this->table($table)->insert($values);
    }

    /**
     * Create a new update query builder
     *
     *     $h->update('users', ['age' => 25])->where('name', 'Johanna')
     *
     * @param array                                  $values
     * @return Update
     */
    public function update($table = null, array $values = []): Update
    {
        return $this->table($table)->update($values);
    }

    /**
     * Create a new delete sql builder
     *
     *     $h->delete('users')->where('age', '<', '18')
     *
     * @return Delete
     */
    public function delete($table = null): Delete
    {
        return $this->table($table)->delete();
    }

    /**
     * Create a new drop table query
     *
     *     $h->drop('users')
     *
     * @return Drop
     */
    public function drop($table = null): Drop
    {
        return $this->table($table)->drop();
    }

    /**
     * Create a new truncate table query
     *
     *     $h->truncate('users')
     *
     * @return Truncate
     */
    public function truncate($table = null): Truncate
    {
        return $this->table($table)->truncate();
    }

    /**
     * Creates a new sql value keyword, limited to 'null', 'default', 'true', 'false', 'unknown'
     *
     * @param string                $value
     * @return SpecialValue
     */
    final public function value(string $value): SpecialValue
    {
        return new SpecialValue($value);
    }

    /**
     * Forces an operand to be treated as a field
     *
     * @param string                $field
     * @return Field
     */
    final public function field(string $field): Field
    {
        return new Field($field);
    }
}
