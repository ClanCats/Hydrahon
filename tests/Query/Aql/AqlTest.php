<?php 

namespace ClanCats\Hydrahon\Test\Query\Aql;

/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Query
 * @group Hydrahon_Query_Aql
 */

use ClanCats\Hydrahon\Query\Sql\BaseSql;

class AqlTest extends \ClanCats\Hydrahon\Test\QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Aql';

	/**
	 * Aql::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);

		$this->assertAttributes($query);
	}

	/**
	 * Aql::for
	 */
	public function testFor()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->each('user', 'users'), array(
			'for' => 'user',
			'in' => 'users'
		));

		// in string 
		$this->assertAttributes($this->createQuery()->each('u in users'), array(
			'for' => 'u',
			'in' => 'users'
		));

		// as string 
		$this->assertAttributes($this->createQuery()->each('posts as post'), array(
			'for' => 'post',
			'in' => 'posts'
		));
	}

	/**
	 * Aql::limit
	 */
	public function testLimit()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->limit(10), array('limit' => 10));

		// with offset
		$this->assertAttributes($this->createQuery()->limit(20, 10), array('limit' => 10, 'offset' => 20));

		// test reset of limit
		$this->assertAttributes($this->createQuery()->limit(10)->limit(null));
	}

	/**
	 * Aql::offset
	 */
	public function testOffset()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->offset(5), array('offset' => 5));

		// test change of offset
		$this->assertAttributes($this->createQuery()->limit(2, 5)->offset(3), array('limit' => 5, 'offset' => 3));
	}

	/**
	 * Aql::page
	 */
	public function testPage()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->page(0), array('limit' => 25));

		// next page
		$this->assertAttributes($this->createQuery()->page(2), array('limit' => 25, 'offset' => 50));

		// custom page size
		$this->assertAttributes($this->createQuery()->page(2, 5), array('limit' => 5, 'offset' => 10));
	}
}