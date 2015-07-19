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
 * @group Hydrahon_Query_Sql_Drop
 */

class Query_Sql_Drop_Test extends Query_QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Sql\\Drop';

	/**
	 * Drop::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);
		$this->assertAttributes($query);
	}
}