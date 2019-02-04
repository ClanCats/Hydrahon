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
 */

use ClanCats\Hydrahon\Query\Sql\Func;
use ClanCats\Hydrahon\Query\Sql\Select;
use ClanCats\Hydrahon\Query\Expression;

class Query_Sql_Select_Test extends Query_QueryCase
{
	protected $queryClass = 'ClanCats\\Hydrahon\\Query\\Sql\\Select';

	/**
	 * Select::construct
	 */
	public function testConstruct()
	{
		$query = $this->createQuery();
		$this->assertInstanceOf($this->queryClass, $query);
		$this->assertAttributes($query);
	}

	/**
	 * Select::distinct
	 */
	public function testDistinct()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->distinct(), array('distinct' => true));

		// disable
		$this->assertAttributes($this->createQuery()->distinct(false));
	}

	/**
	 * Select::fields
	 */
	public function testFields()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->fields(array('id', 'name')), array('fields' => array(array('id', null), array('name', null))));

		// single filed
		$this->assertAttributes($this->createQuery()->fields('title'), array('fields' => array(array('title', null))));

		// comma seperated string
		$this->assertAttributes($this->createQuery()->fields('id, name   , title'), array('fields' => array(array('id', null), array('name', null), array('title', null))));

		// empty 
		$this->assertAttributes($this->createQuery()->fields(''));
		$this->assertAttributes($this->createQuery()->fields('*'));
		$this->assertAttributes($this->createQuery()->fields(array()));

		// with alias
		$this->assertAttributes($this->createQuery()->fields(array('page_title' => 'title')), array('fields' => array(array( 'page_title', 'title' ))));

		// with raw expression
		$field = new Expression('max(views)');
		$this->assertAttributes($this->createQuery()->fields($field), array('fields' => array(array($field, null))));

		// raw expression in array
		$field = new Expression('max(views)');
		$this->assertAttributes($this->createQuery()->fields(array($field, $field)), array('fields' => array(array($field, null), array($field, null))));

		// with sql function
		$field = new Func('max', 'views');
		$this->assertAttributes($this->createQuery()->fields($field), array('fields' => array(array($field, null))));

	}

	/**
	 * Select::fields
	 */
	public function testAddField()
	{
		$query = $this->createQuery()->fields(array('id', 'name'));

		// check
		$this->assertAttributes($query, array('fields' => array(array('id', null), array('name', null))));

		// add a field
		$this->assertAttributes($query->addField('created_at'), array('fields' => array(array('id', null), array('name', null), array('created_at', null))));	

		// add another one
		$this->assertAttributes($query->addField('active', 'a'), array('fields' => array(array('id', null), array('name', null), array('created_at', null), array('active', 'a'))));	

		// test add function field
		$field = new Func('max', 'views');
		$this->assertAttributes($this->createQuery()->addField($field, 'max_views'), array('fields' => array(array($field, 'max_views'))));
	}

	/**
	 * Select::orderBy
	 */
	public function testOrderBy()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->orderBy('id'), array('orders' => array('id' => 'asc')));

		// other direction 
		$this->assertAttributes($this->createQuery()->orderBy('id', 'desc'), array('orders' => array('id' => 'desc')));

		// multiple same direction
		$this->assertAttributes($this->createQuery()->orderBy(array('name', 'created_at'), 'desc'), array('orders' => array('name' => 'desc', 'created_at' => 'desc')));

		// multiple same direction as string
		$this->assertAttributes($this->createQuery()->orderBy('name, created_at', 'desc'), array('orders' => array('name' => 'desc', 'created_at' => 'desc')));

		// multiple other direction
		$this->assertAttributes($this->createQuery()->orderBy(array('firstname' => 'asc', 'lastname' => 'desc')), array('orders' => array('firstname' => 'asc', 'lastname' => 'desc')));
	}

	/**
	 * Select::orderBy
	 */
	public function testOrderByRaw()
	{
		$raw = new Expression('language <> de');

		// simple 
		$this->assertAttributes($this->createQuery()->orderBy($raw), array('orders' => array(array($raw, 'asc'))));

		// in array 
		$this->assertAttributes($this->createQuery()->orderBy([$raw, $raw]), array('orders' => array(array($raw, 'asc'), array($raw, 'asc'))));

		// in array order
		$this->assertAttributes($this->createQuery()->orderBy([$raw, $raw], 'desc'), array('orders' => array(array($raw, 'desc'), array($raw, 'desc'))));

		// in array mixing
		$this->assertAttributes($this->createQuery()->orderBy([$raw, 'foo' => 'asc'], 'desc'), array('orders' => array(array($raw, 'desc'), 'foo' => 'asc')));
	}

	/**
	 * Select::groupBy
	 */
	public function testGroupBy()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->groupBy('category'), array('groups' => array('category')));

		// multiple
		$this->assertAttributes($this->createQuery()->groupBy('category, age'), array('groups' => array('category', 'age')));
		$this->assertAttributes($this->createQuery()->groupBy(array('category', 'age')), array('groups' => array('category', 'age')));
	}

	/**
	 * Select::join
	 */
	public function testJoin()
	{
		// simple 
		$this->assertAttributes($this->createQuery()->join('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('left', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// left
		$this->assertAttributes($this->createQuery()->leftJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('left', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// right
		$this->assertAttributes($this->createQuery()->rightJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('right', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// inner
		$this->assertAttributes($this->createQuery()->innerJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('inner', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// outer
		$this->assertAttributes($this->createQuery()->outerJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('outer', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// join with raw values
		$expression = new Expression("`avatars`.`user_id` and `avatars`.`active` = 1");

		$this->assertAttributes(
			$this->createQuery()->join('avatars', 'users.id', '=', new Expression("`avatars`.`user_id` and `avatars`.`active` = 1")), 
			array('joins' => array(array('left', 'avatars', 'users.id', '=', $expression)))
		);
	}

	/**
	 * Select::join
	 */
	public function testJoinCallbacks()
	{
		// simple clojure
		$this->assertAttributes($this->createQuery()
		->join('avatars', function($join) {
			$join->on('user.id', '=', 'avatars.user_id');
		}), array(
			'joins' => array(
				array('left', 'avatars', array( 'ons' => array(
					array('and', 'user.id', '=', 'avatars.user_id'),
				))),
			)
		));

		// with or
		$this->assertAttributes($this->createQuery()
		->join('avatars', function($join) {
			$join->on('user.id', '=', 'avatars.user_id');
			$join->orOn('user.id', '=', 'avatars.other_user_id');
		}), array(
			'joins' => array(
				array('left', 'avatars', array( 'ons' => array(
					array('and', 'user.id', '=', 'avatars.user_id'),
					array('or', 'user.id', '=', 'avatars.other_user_id'),
				))),
			)
		));

		// with or and wheres
		$this->assertAttributes($this->createQuery()
		->join('avatars', function($join) {
			$join->on('user.id', '=', 'avatars.user_id');
			$join->orOn('user.id', '=', 'avatars.other_user_id');
			$join->where('avatars.active', 1);
		}), array(
			'joins' => array(
				array('left', 'avatars', array( 
					'ons' => array(
						array('and', 'user.id', '=', 'avatars.user_id'),
						array('or', 'user.id', '=', 'avatars.other_user_id'),
					),
					'wheres' => array(
						array('where', 'avatars.active', '=', 1),
					),
				)),
			)
		));
	}

	/**
	 * Select::run
	 */
	public function testRun()
	{
		$data = array(array('id' => 1, 'name' => 'foo'));

		// simple 
		$select = $this->createQuery($data);
		$this->assertEquals($data, $select->get());

		// just one
		$select->limit(1);
		$this->assertEquals(reset($data), $select->get());

		// invalid data array
		$select = $this->createQuery('nope');
		$this->assertEquals(array(), $select->get());

		// no item found
		$select = $this->createQuery(array())->limit(1);
		$this->assertEquals(false, $select->get());
	}

	/**
	 * Select::forwardKey
	 */
	public function testForwardKey()
	{
		$data = array(
			array('id' => 1, 'name' => 'Mario', 'age' => 23),
			array('id' => 2, 'name' => 'Johnna', 'age' => 20),
		);

		$select = $this->createQuery($data);

		// control
		$this->assertEquals($data, $select->get());

		// now group by age
		$select->forwardKey('name');
		$result = $select->get();

		$this->assertEquals(23, $result['Mario']['age']);
		$this->assertEquals(20, $result['Johnna']['age']);
	}

	/**
	 * Select::groupResults
	 */
	public function testGroupResults()
	{
		$data = array(
			array('id' => 1, 'name' => 'Mario', 'age' => 23),
			array('id' => 2, 'name' => 'Johnna', 'age' => 20),
			array('id' => 3, 'name' => 'Tarek', 'age' => 22),
			array('id' => 4, 'name' => 'Michel', 'age' => 22),
			array('id' => 5, 'name' => 'Johnna', 'age' => 22),
		);

		$select = $this->createQuery($data);

		// control
		$this->assertEquals($data, $select->get());

		// now group by age
		$select->groupResults('age');
		$grouped = $select->get();

		$this->assertCount(1, $grouped[23]);
		$this->assertCount(1, $grouped[20]);
		$this->assertCount(3, $grouped[22]);

		// now also forward the keys
		$select->forwardKey('id');
		$grouped = $select->get();

		$this->assertEquals('Johnna', $grouped[22][5]['name']);
		$this->assertEquals('Johnna', $grouped[20][2]['name']);
	}
}
