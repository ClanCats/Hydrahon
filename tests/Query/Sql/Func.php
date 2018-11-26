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
 * @group Hydrahon_Query_Sql_Func
 */

use ClanCats\Hydrahon\Query\Sql\Func;

class Query_Sql_Func_Test extends \PHPUnit\Framework\TestCase
{
	/**
	 * Func::construct test
	 */
	public function testConstruct()
	{
		$function = new Func('foo');
		$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Func', $function);
	}

	/**
	 * Func::construct test
	 * 
	 * @expectedException Exception
	 */
	public function testConstructWithoutName()
	{
		new Func();
	}

	/**
	 * Func::name test
	 */
	public function testName()
	{
		$function = new Func('count');

		$this->assertEquals('count', $function->name());
	}

	/**
	 * Func::arguments test
	 */
	public function testarguments()
	{
		$function = new Func('count', '*');
		$this->assertEquals(array('*'), $function->arguments());

		// multiple
		$function = new Func('max', 'foo', 'bar');
		$this->assertEquals(array('foo', 'bar'), $function->arguments());
	}
}