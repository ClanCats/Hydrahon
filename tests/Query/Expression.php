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
 * @group Hydrahon_Query_Expression
 */

use ClanCats\Hydrahon\Query\Expression;

class Expression_Test extends \PHPUnit\Framework\TestCase
{
	/**
	 * Expression::construct test
	 */
	public function testConstruct()
	{
		$expression = new Expression('foo');
		$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Expression', $expression);
	}

	/**
	 * Expression::value test
	 */
	public function testValue()
	{
		$expression = new Expression('foo');

		$this->assertEquals('foo', (string)$expression);
		$this->assertEquals('foo', $expression->value());
	}
}