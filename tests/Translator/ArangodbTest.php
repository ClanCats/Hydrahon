<?php 

namespace ClanCats\Hydrahon\Test\Translator;

/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Translator
 * @group Hydrahon_Translator_Arangodb
 */

use ClanCats\Hydrahon\Query\Sql\BaseSql;
use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\Query\Sql\Func;

class ArangodbTest extends \ClanCats\Hydrahon\Test\TranslatorCase
{
	protected $grammar = 'arango';

	/**
	 * mysql grammar tests
	 */
	public function testForSimple()
	{
		$this->assertQueryTranslation('FOR `u` IN `users`', array(), function($q) 
		{
			return $q->each('u', 'users');
		});

		$this->assertQueryTranslation('FOR `u` IN `users`', array(), function($q) 
		{
			return $q->each('users as u');
		});

		$this->assertQueryTranslation('FOR `u` IN `users`', array(), function($q) 
		{
			return $q->each('u in users');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testLimitAndOffset()
	{
		$this->assertQueryTranslation('FOR `u` IN `users` LIMIT 0, 10', array(), function($q) 
		{
			return $q->each('u', 'users')->limit(10);
		});

		$this->assertQueryTranslation('FOR `u` IN `users` LIMIT 20, 10', array(), function($q) 
		{
			return $q->each('u', 'users')->limit(20, 10);
		});

		$this->assertQueryTranslation('FOR `u` IN `users` LIMIT 50, 0', array(), function($q) 
		{
			return $q->each('u', 'users')->offset(50);
		});
	}
}