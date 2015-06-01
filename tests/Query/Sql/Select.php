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

use ClanCats\Hydrahon\Query\Sql\Select;

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
		$this->assertAttributes($this->createQuery()->distinct(), array('distinct' => true ));

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
		$this->assertAttributes($this->createQuery()->join('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array(null, 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// left
		$this->assertAttributes($this->createQuery()->leftJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('left', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// right
		$this->assertAttributes($this->createQuery()->rightJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('right', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// inner
		$this->assertAttributes($this->createQuery()->innerJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('inner', 'avatars', 'users.id', '=', 'avatars.user_id'))));

		// outer
		$this->assertAttributes($this->createQuery()->outerJoin('avatars', 'users.id', '=', 'avatars.user_id'), array('joins' => array(array('outer', 'avatars', 'users.id', '=', 'avatars.user_id'))));
	}
}