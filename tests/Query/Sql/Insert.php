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
 * @group Hydrahon_Query_Sql_Insert
 */

use ClanCats\Hydrahon\Query\Sql\Select;

class Query_Sql_Insert_Test extends Query_QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Sql\\Insert';

	/**
	 * Insert::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);
		$this->assertAttributes($query);
	}

	/**
	 * Insert::ignore
	 */
	public function testIgnore()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->ignore(), array('ignore' => true));

		// disable
		$this->assertAttributes($this->createQuery()->ignore(false));
	}

	/**
	 * Insert::values
	 */
	public function testValues()
	{
		// lets get started with something simple
		$values = array('foo' => 'bar');
		$this->assertAttributes($this->createQuery()->values($values), array('values' => array($values)));

		// more values
		$values = array('foo' => 'bar', 'bar' => 'foo');
		$this->assertAttributes($this->createQuery()->values($values), array('values' => array($values)));

		// Bulk
		$values = array(
			array('foo' => 'bar', 'bar' => 'foo'),
			array('bar' => 'foo', 'foo' => 'bar'),
		);
		$this->assertAttributes($this->createQuery()->values($values), array('values' => $values));

		// empty insert
		$this->assertAttributes($this->createQuery()->values(array()));
	}

	/**
	 * Insert::resetValues
	 */
	public function testResetValues()
	{
		$values = array('foo' => 'bar');
		$query = $this->createQuery()->values($values);
		$this->assertAttributes($query, array('values' => array($values)));

		$query->resetValues();

		// empty insert
		$this->assertAttributes($query);
	}
}