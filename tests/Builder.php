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

class Builder_Test extends \PHPUnit\Framework\TestCase
{
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
		Builder::extend('invalidtranslator',  'ClanCats\\Hydrahon\\Query\\Sql', 'ClanCats\\Hydrahon\\Exception');

		// but now it should fail
		$hydrahon = new Builder('invalidtranslator', function() {});
	}

	/**
	 * Builder::constuct test
	 *
	 * @expectedException ClanCats\Hydrahon\Exception
	 */
	public function testConsturctInvalidBuilderClass()
	{	
		Builder::extend('invalidBuilder', 'ClanCats\\Hydrahon\\Exception','ClanCats\\Hydrahon\\Translator\\Mysql');
		// but now it should fail
		$hydrahon = new Builder('invalidtranslator', function() {});
	}

	/**
	 * Check query classes
	 */
	public function testQueryClassesAsExpected()
	{
		// simple select
		$hydrahon = new Builder('mysql', function($query, $queryString, $queryParameters) 
		{
			$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Select', $query);
			$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\FetchableInterface', $query);
		});

		$hydrahon->table('test')->select()->get();

		// exists select
		$hydrahon = new Builder('mysql', function($query, $queryString, $queryParameters) 
		{
			$this->assertNotInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Select', $query);
			$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Exists', $query);
			$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\FetchableInterface', $query);
		});

		$hydrahon->table('test')->select()->exists();

		// non select
		$hydrahon = new Builder('mysql', function($query, $queryString, $queryParameters) 
		{
			$this->assertNotInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Select', $query);
			$this->assertNotInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\FetchableInterface', $query);
			$this->assertInstanceOf('ClanCats\\Hydrahon\\Query\\Sql\\Insert', $query);
		});

		$hydrahon->table('test')->insert(['foo' => 'bar'])->execute();
	}
}
