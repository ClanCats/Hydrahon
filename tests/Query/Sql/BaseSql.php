<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Query
 * @group Hydrahon_Query_Sql
 */

use ClanCats\Hydrahon\Query\Sql\BaseSql;

class Query_Sql_BaseSql_Test extends Query_QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Sql\\SelectBase';

	/**
	 * BaseSql::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);

		$this->assertAttributes($query);
	}

	/**
	 * BaseSql::where
	 */
	public function testWhere()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->where('id', 42), array(
			'wheres' => array(
				array('where', 'id', '=', '42')
			)
		));

		// simple with other expression
		$this->assertAttributes($this->createQuery()->where('id', '!=', 42), array(
			'wheres' => array(
				array('where', 'id', '!=', '42')
			)
		));

		// array parameter
		$this->assertAttributes($this->createQuery()->where('id', 'in', array(1, 2, 3)), array(
			'wheres' => array(
				array('where', 'id', 'in', array(1, 2, 3))
			)
		));

		// 2 wheres should be be and
		$this->assertAttributes($this->createQuery()->where('age', '>', 18)->where('active', 1), array(
			'wheres' => array(
				array('where', 'age', '>', 18),
				array('and', 'active', '=', 1)
			)
		));

		// 2 wheres with sepcified or
		$this->assertAttributes($this->createQuery()->where('age', '>', 18)->where('active', '=', 1, 'or'), array(
			'wheres' => array(
				array('where', 'age', '>', 18),
				array('or', 'active', '=', 1)
			)
		));
	}

	/**
	 * BaseSql::whereReset
	 */
	public function testWhereReset()
	{
		$this->assertAttributes($this->createQuery()->where('id', 42)->resetWheres(), array());
	}

	/**
	 * BaseSql::orWhere
	 */
	public function testOrWhere()
	{
		// simple should still be where
		$this->assertAttributes($this->createQuery()->orWhere('id', 42), array(
			'wheres' => array(
				array('where', 'id', '=', 42)
			)
		));

		// simple should still be where
		$this->assertAttributes($this->createQuery()->orWhere('id', 42)->orWhere('id', 5), array(
			'wheres' => array(
				array('where', 'id', '=', 42),
				array('or', 'id', '=', 5)
			)
		));
	}

	/**
	 * BaseSql::andWhere
	 */
	public function testAndWhere()
	{
		// simple should still be where
		$this->assertAttributes($this->createQuery()->andWhere('id', 42), array(
			'wheres' => array(
				array('where', 'id', '=', 42)
			)
		));

		// simple should still be where
		$this->assertAttributes($this->createQuery()->andWhere('id', 42)->andWhere('id', 5), array(
			'wheres' => array(
				array('where', 'id', '=', 42),
				array('and', 'id', '=', 5)
			)
		));
	}

	/**
	 * BaseSql::whereIn
	 */
	public function testWhereIn()
	{
		// simple should still be where
		$this->assertAttributes($this->createQuery()->whereIn('id', array(42, 31, 21)), array(
			'wheres' => array(
				array('where', 'id', 'in', array(42, 31, 21))
			)
		));
	}

	/**
	 * BaseSql::whereNotIn
	 */
	public function testWhereNotIn()
	{
		$this->assertAttributes($this->createQuery()->whereNotIn('id', array(42, 31, 21)), array(
			'wheres' => array(
				array('where', 'id', 'not in', array(42, 31, 21))
			)
		));
	}

	/**
	 * BaseSql::where
	 * 
	 * @expectedException ClanCats\Hydrahon\Query\Sql\Exception
	 */
	public function testWhereInvalidType()
	{
		$this->createQuery()->where('age', '>', 18)->where('active', '=', 1, 'thisisnotvalid');
	}

	/**
	 * BaseSql::where
	 */
	public function testWhereBulk()
	{
		$this->assertAttributes($this->createQuery()->where(array('title' => 'foo', 'subtitle' => 'bar')), array(
			'wheres' => array(
				array('where', array( 'wheres' => array(
					array('where', 'title', '=', 'foo'),
					array('and', 'subtitle', '=', 'bar')
				)))
			)
		));


		// multiple bulk
		$this->assertAttributes($this->createQuery()
			->where(array('title' => 'foo', 'subtitle' => 'bar'))
			->where(array('age' => 18, 'active' => 1))
			, array(
			'wheres' => array(
				array('where', array( 'wheres' => array(
					array('where', 'title', '=', 'foo'),
					array('and', 'subtitle', '=', 'bar')
				))),
				array('and', array( 'wheres' => array(
					array('where', 'age', '=', 18),
					array('and', 'active', '=', 1)
				)))
			)
		));
	}

	/**
	 * BaseSql::where
	 */
	public function testWhereClosure()
	{
		$this->assertAttributes($this->createQuery()
		->where(function($query) {
			$query->whereIn('id', array(1, 42, 43));
			$query->orWhere('foo', 'bar');
		})
		->orWhere(function($query) {
			$query->where('super_active', 1);
			$query->andWhere('inactive', 0);
		}), array(
			'wheres' => array(
				array('where', array( 'wheres' => array(
					array('where', 'id', 'in', array(1, 42, 43)),
					array('or', 'foo', '=', 'bar')
				))),
				array('or', array( 'wheres' => array(
					array('where', 'super_active', '=', 1),
					array('and', 'inactive', '=', 0)
				)))
			)
		));
	}

	/**
	 * BaseSql::limit
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
	 * BaseSql::limitReset
	 */
	public function testLimitReset()
	{
		$this->assertAttributes($this->createQuery()->limit(10)->resetLimit(), array());
	}

	/**
	 * BaseSql::offset
	 */
	public function testOffset()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->offset(5), array('offset' => 5));

		// test change of offset
		$this->assertAttributes($this->createQuery()->limit(2, 5)->offset(3), array('limit' => 5, 'offset' => 3));
	}

	/**
	 * BaseSql::offsetReset
	 */
	public function testOffsetReset()
	{
		$this->assertAttributes($this->createQuery()->offset(10)->resetOffset(), array());
	}

	/**
	 * BaseSql::page
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

	/**
	 * BaseSql::page
	 */
	public function testOverwriteAttributes()
	{
		$query = $this->createQuery();
		$query->where('id', 42);

		$this->assertAttributes($query, array('wheres' => array(array('where', 'id', '=', 42))));

		// overwrite the wheres
		$query->overwriteAttributes(array('wheres' => array()));
		$this->assertAttributes($query, array());
	}
}