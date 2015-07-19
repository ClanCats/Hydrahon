<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon query test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Query
 * @group Hydrahon_Query_Sql
 * @group Hydrahon_Query_Sql_Update
 */

use ClanCats\Hydrahon\Query\Sql\Select;

class Query_Sql_Update_Test extends Query_QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Sql\\Update';

	/**
	 * Update::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);
		$this->assertAttributes($query);
	}

	/**
	 * Update::values
	 */
	public function testValues()
	{
		// lets get started with something simple
		$values = array('foo' => 'bar');
		$this->assertAttributes($this->createQuery()->set($values), array('values' => $values));

		// more values
		$values = array('foo' => 'bar', 'bar' => 'foo');
		$this->assertAttributes($this->createQuery()->set($values), array('values' => $values));

		// single set
		$this->assertAttributes($this->createQuery()->set('foo', 'bar'), array('values' => array('foo' => 'bar')));

		// multi set
		$this->assertAttributes($this->createQuery()->set('foo', 'bar')->set('bar', 'foo'), array('values' => array('foo' => 'bar', 'bar' => 'foo')));

		// empty insert
		$this->assertAttributes($this->createQuery()->set(array()));
	}
}