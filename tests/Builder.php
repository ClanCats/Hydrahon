<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Builder
 */

use ClanCats\Hydrahon\Builder;

class Builder_Test extends \PHPUnit_Framework_TestCase
{
	/**
	 * Builder::extend test
	 */
	public function testExtend()
	{
		Builder::extend('phpunit', '\\This\\Should\\Work', '\\Without\\The\\Class\\Existing');
	}

	/**
	 * Builder::extend test
	 * 
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testExtendOverwriteError()
	{
		Builder::extend('mysql', 'This will', 'trigger an error');
	}

	/**
	 * Builder::construct test
	 */
	public function testConsturct()
	{	
		$hydrahon = new Builder('mysql', function() {});
		$this->assertInstanceOf( 'ClanCats\\Hydrahon\\Builder', $hydrahon );
	}

	/**
	 * Builder::construct test
	 *
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testConsturctInvalidGrammar()
	{	
		$hydrahon = new Builder('notexisting', function() {});
	}

	/**
	 * Builder::construct test
	 *
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testConsturctInvalidCallback()
	{	
		$hydrahon = new Builder('mysql', 123);
	}

	/**
	 * Builder::table test
	 *
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testConsturctInvalidTableArgument()
	{	
		$hydrahon = new Builder('mysql', function() {});
		$hydrahon->table('foo.bar.fail');
	}

	/**
	 * Builder::constuct test
	 *
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testConsturctInvalidTransltorClass()
	{	
		Builder::extend('invalidtranslator', '\\This\\Should\\Work', '\\ClanCats\\Hydrahon\\Exception');
		// but now it should faile
		$hydrahon = new Builder('invalidtranslator', function() {});
	}
}