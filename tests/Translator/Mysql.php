<?php namespace ClanCats\Hydrahon\Test;
/**
 * Hydrahon builder test 
 ** 
 *
 * @package 		Hydrahon
 * @copyright 		Mario Döring
 *
 * @group Hydrahon
 * @group Hydrahon_Translator
 * @group Hydrahon_Translator_Mysql
 */

use ClanCats\Hydrahon\Query\Sql\BaseSql;
use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\Query\Sql\Func;

class Translator_Mysql_Test extends TranslatorCase
{
	protected $grammar = 'mysql';

	/**
	 * mysql grammar tests
	 */
	public function testSelectSimple()
	{
		$this->assertQueryTranslation('select * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select();
		});

		// distinct
		$this->assertQueryTranslation('select distinct * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->distinct();
		});

		// with database 
		$this->assertQueryTranslation('select distinct * from `db_phpunit`.`users` as `u`', array(), function($q) 
		{
			return $q->table('db_phpunit.users as u')->select()->distinct();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testTable()
	{
		$this->assertQueryTranslation('select * from `phpunit``b`', array(), function($q) 
		{
			return $q->table('phpunit`b')->select();
		});

		$this->assertQueryTranslation('select * from `phpunit` as `foo`', array(), function($q) 
		{
			return $q->table('phpunit', 'foo')->select();
		});

		$this->assertQueryTranslation('select * from `db`.`phpunit` as `foo`', array(), function($q) 
		{
			return $q->table('db.phpunit', 'foo')->select();
		});

		$this->assertQueryTranslation('select * from `db`.`phpunit` as `foo`', array(), function($q) 
		{
			return $q->table('db.phpunit as foo')->select();
		});

		$this->assertQueryTranslation('select * from `phpunit` as `foo`', array(), function($q) 
		{
			return $q->table('phpunit as foo')->select();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectFields()
	{
		$this->assertQueryTranslation('select `id` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('id');
		});

		// comma seperated fields
		$this->assertQueryTranslation('select `foo`, `bar` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('foo, bar');
		});

		// with alias as string
		$this->assertQueryTranslation('select `foo`, `bar` as `b` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select('foo, bar as b');
		});

		// with array
		$this->assertQueryTranslation('select `name`, `age` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array('name', 'age'));
		});

		// with array with alias
		$this->assertQueryTranslation('select `name` as `n`, `age` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array('name' => 'n', 'age'));
		});

		// with raw 
		$this->assertQueryTranslation('select count(id) as count from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(array(new Expression('count(id) as count')));
		});

		// with raw outside of the array 
		$this->assertQueryTranslation('select count(id) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(new Expression('count(id)'));
		});

		// with raw function
		$this->assertQueryTranslation('select count(`id`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select(new Func('count', 'id'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddField()
	{
		$this->assertQueryTranslation('select `foo` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addField('foo');
		});

		// test with alias
		$this->assertQueryTranslation('select `foo` as `bar` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addField('foo', 'bar');
		});

		// add field with function
		$this->assertQueryTranslation('select max(`views`) as `max_views` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addField(new Func('max', 'views'), 'max_views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldCount()
	{
		$this->assertQueryTranslation('select count(`id`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldCount('id');
		});

		// with alias
		$this->assertQueryTranslation('select count(`id`) as `count` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldCount('id', 'count');
		});

		// with raw 
		$this->assertQueryTranslation('select count(*) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldCount(new Expression('*'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldMax()
	{
		$this->assertQueryTranslation('select max(`views`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldMax('views');
		});

		// with alias
		$this->assertQueryTranslation('select max(`views`) as `max_views` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldMax('views', 'max_views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldMin()
	{
		$this->assertQueryTranslation('select min(`views`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldMin('views');
		});

		// with alias
		$this->assertQueryTranslation('select min(`views`) as `min_views` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldMin('views', 'min_views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldSum()
	{
		$this->assertQueryTranslation('select sum(`views`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldSum('views');
		});

		// with alias
		$this->assertQueryTranslation('select sum(`views`) as `total_views` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldSum('views', 'total_views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldAvg()
	{
		$this->assertQueryTranslation('select avg(`views`) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldAvg('views');
		});

		// with alias
		$this->assertQueryTranslation('select avg(`views`) as `average_views` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldAvg('views', 'average_views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectAddFieldRound()
	{
		$this->assertQueryTranslation('select round(`price`, 0) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldRound('price');
		});

		// with custom decimal
		$this->assertQueryTranslation('select round(`price`, 2) from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldRound('price', 2);
		});

		// with alias
		$this->assertQueryTranslation('select round(`price`, 2) as `rounded_price` from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->addFieldRound('price', 2, 'rounded_price');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testWhere()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ?', array(42), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', 42);
		});

		// diffrent expression
		$this->assertQueryTranslation('select * from `phpunit` where `id` != ?', array(42), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', '!=', 42);
		});

		// raw value
		$this->assertQueryTranslation('select * from `phpunit` where `id` != 42', array(), function($q) 
		{
			return $q->table('phpunit')->select()->where('id', '!=', new Expression('42'));
		});

		// 2 wheres
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ? and `active` = ?', array(42, 1), function($q) 
		{
			return $q->table('phpunit')->select()
				->where('id', 42 )
				->where('active', 1);
		});

		// 2 wheres or
		$this->assertQueryTranslation('select * from `phpunit` where `id` = ? or `active` = ?', array(42, 1), function($q) 
		{
			return $q->table('phpunit')->select()
				->where('id', 42 )
				->orWhere('active', 1);
		});

		// nesting
		$this->assertQueryTranslation('select * from `phpunit` where ( `a` = ? or `c` = ? )', array('b', 'd'), function($q) 
		{
			return $q->table('phpunit')->select()
				->where(function( $q )
				{
					$q->where('a', 'b');
					$q->orWhere('c', 'd');
				});
		});

		// arrays
		$this->assertQueryTranslation('select * from `phpunit` where ( `name` = ? and `age` = ? )', array('foo', 18), function($q) 
		{
			return $q->table('phpunit')->select()
				->where(array( 'name' => 'foo', 'age' => 18 ));
		});

		//  where in
		$this->assertQueryTranslation('select * from `phpunit` where `id` in (?, ?, ?)', array(23, 213, 53), function($q) 
		{
			return $q->table('phpunit')->select()
				->whereIn('id', array(23, 213, 53));
		});

		// where not in
		$this->assertQueryTranslation('select * from `phpunit` where `id` not in (?, ?, ?)', array(23, 213, 53), function($q)
		{
			return $q->table('phpunit')->select()
				->whereNotIn('id', array(23, 213, 53));
		});

		//  where null
		$this->assertQueryTranslation('select * from `phpunit` where `user`.`updated` is NULL', array(), function($q) 
		{
			return $q->table('phpunit')->select()
				->whereNull('user.updated');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testLimit()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` limit 0, 1', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit(1);
		});

		// with offset
		$this->assertQueryTranslation('select * from `phpunit` limit 10, 20', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit(10, 20);
		});

		// invalid stuff
		$this->assertQueryTranslation('select * from `phpunit`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->limit('ignore');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectOrderBy()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` order by `id` asc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('id');
		});

		// other direction
		$this->assertQueryTranslation('select * from `phpunit` order by `id` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('id', 'desc');
		});

		// more keys comma seperated
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` desc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy('u.firstname, u.lastname', 'desc');
		});

		// column array
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` desc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy(array('u.firstname', 'u.lastname'), 'desc');
		});

		// multipe sortings diffrent direction
		$this->assertQueryTranslation('select * from `phpunit` order by `u`.`firstname` asc, `u`.`lastname` desc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy(array('u.firstname' => 'asc', 'u.lastname' => 'desc'));
		});

		// raw sorting
		$this->assertQueryTranslation('select * from `phpunit` order by firstname <> nick asc', array(), function($q) 
		{
			return $q->table('phpunit')->select()->orderBy(new Expression("firstname <> nick"));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectGroupBy()
	{
		// simple
		$this->assertQueryTranslation('select * from `phpunit` group by `user`.`group`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy('user.group');
		});

		// comma seperated
		$this->assertQueryTranslation('select * from `phpunit` group by `user`.`group`, `group`.`key`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy('user.group, group.key');
		});

		// array
		$this->assertQueryTranslation('select * from `phpunit` group by `foo`, `bar`', array(), function($q) 
		{
			return $q->table('phpunit')->select()->groupBy(array('foo', 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectJoins()
	{
		// simple
		$this->assertQueryTranslation('select * from `db1`.`users` as `u` left join `db1`.`groups` as `g` on `u`.`id` = `g`.`user_id`', array(), function($q) 
		{
			return $q->table('db1.users as u')->select()->join('db1.groups as g', 'u.id', '=', 'g.user_id');
		});

		// other types
		$this->assertQueryTranslation('select * from `db1`.`users` as `u` left join `db1`.`groups` as `g` on `u`.`id` = `g`.`user_id` right join `db1`.`orders` as `o` on `u`.`id` = `o`.`user_id` inner join `profiles` as `p` on `u`.`id` = `p`.`user_id`', array(), function($q) 
		{
			return $q->table('db1.users as u')->select()
				->join('db1.groups as g', 'u.id', '=', 'g.user_id')
				->rightJoin('db1.orders as o', 'u.id', '=', 'o.user_id')
				->innerJoin('profiles as p', 'u.id', '=', 'p.user_id');
		});

		// with raw values
		$this->assertQueryTranslation('select * from `db1`.`users` as `u` left join `db1`.`groups` as `g` on `u`.`id` = `g`.`user_id` AND `g`.active = 1', array(), function($q) 
		{
			return $q->table('db1.users as u')->select()->join('db1.groups as g', 'u.id', '=', new Expression('`g`.`user_id` AND `g`.active = 1'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSelectJoinMulticonditions()
	{
		// simple
		$this->assertQueryTranslation('select * from `users` left join `avatars` on `avatars`.`user_id` = `users`.`id`', array(), function($q) 
		{
			return $q->table('users')->select()
				->join('avatars', function($q) 
				{
					$q->on('avatars.user_id', '=', 'users.id');
				});
		});

		// multiple
		$this->assertQueryTranslation('select * from `users` left join `avatars` on `avatars`.`user_id` = `users`.`id` or `avatars`.`other_user_id` = `users`.`id`', array(), function($q) 
		{
			return $q->table('users')->select()
				->join('avatars', function($q) 
				{
					$q->on('avatars.user_id', '=', 'users.id');
					$q->orOn('avatars.other_user_id', '=', 'users.id');
				});
		});

		// and
		$this->assertQueryTranslation('select * from `users` left join `avatars` on `avatars`.`user_id` = `users`.`id` and `avatars`.`other_user_id` = `users`.`id`', array(), function($q) 
		{
			return $q->table('users')->select()
				->join('avatars', function($q) 
				{
					$q->on('avatars.user_id', '=', 'users.id');
					$q->on('avatars.other_user_id', '=', 'users.id');
				});
		});

		// with wheres
		$this->assertQueryTranslation('select * from `users` left join `avatars` on `avatars`.`user_id` = `users`.`id` and `avatars`.`other_user_id` = `users`.`id` and `avatars`.`active` = ?', array(1), function($q) 
		{
			return $q->table('users')->select()
				->join('avatars', function($q) 
				{
					$q->on('avatars.user_id', '=', 'users.id');
					$q->on('avatars.other_user_id', '=', 'users.id');
					$q->where('avatars.active', 1);
				});
		});

		// with or wheres
		$this->assertQueryTranslation('select * from `users` left join `avatars` on `avatars`.`user_id` = `users`.`id` and `avatars`.`active` = ? or `avatar`.`public` = ?', array(1, 1), function($q) 
		{
			return $q->table('users')->select()
				->join('avatars', function($q) 
				{
					$q->on('avatars.user_id', '=', 'users.id');
					$q->where('avatars.active', 1);
					$q->orWhere('avatar.public', 1);
				});
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertSimple()
	{
		// simple
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar'));
		});

		// some more complexity
		$this->assertQueryTranslation('insert into `test` (`bar`, `foo`) values (?, ?)', array('foo','bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testReplaceSimple()
	{
		// simple
		$this->assertQueryTranslation('replace into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test')->replace()->values(array('foo' => 'bar'));
		});

		// some more complexity
		$this->assertQueryTranslation('replace into `test` (`bar`, `foo`) values (?, ?)', array('foo','bar'), function($q) 
		{
			return $q->table('test')->replace()->values(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertWithUnwantedAlias()
	{
		// simple
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test as foo')->insert()->values(array('foo' => 'bar'));
		});

		// some more complexity
		$this->assertQueryTranslation('insert into `test` (`bar`, `foo`) values (?, ?)', array('foo','bar'), function($q) 
		{
			return $q->table(array('test' => 'nope'))->insert()->values(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertIgnore()
	{
		// ignore
		$this->assertQueryTranslation('insert ignore into `test` (`foo`) values (?)', array('bar'), function($q) 
		{
			return $q->table('test')->insert()->ignore()->values(array('foo' => 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testInsertBulk()
	{
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?), (?)', array('bar', 'bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array(array('foo' => 'bar'), array('foo' => 'bar')));
		});

		// 2x add valies
		$this->assertQueryTranslation('insert into `test` (`foo`) values (?), (?)', array('bar', 'bar'), function($q) 
		{
			return $q->table('test')->insert()->values(array('foo' => 'bar'))->values(array('foo' => 'bar'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testUpdateSimple()
	{
		// simple
		$this->assertQueryTranslation('update `test` set `foo` = ?', array('bar'), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar'));
		});

		$this->assertQueryTranslation('update `test` set `foo` = ?, `bar` = ?', array('bar', 'foo'), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar', 'bar' => 'foo'));
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testUpdateWithWhereAndLimit()
	{
		// simple
		$this->assertQueryTranslation('update `test` set `foo` = ? where `id` = ? limit 1', array('bar', 1), function($q) 
		{
			return $q->table('test')->update()->set(array('foo' => 'bar'))->where('id', 1)->limit(1);
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testDelete()
	{
		// simple
		$this->assertQueryTranslation('delete from `test` where `id` = ? limit 1', array(1), function($q) 
		{
			return $q->table('test')->delete()->where('id', 1)->limit(1);
		});

		// simple with db
		$this->assertQueryTranslation('delete from `db`.`test` where `id` = ? limit 1', array(1), function($q) 
		{
			return $q->table('db.test')->delete()->where('id', 1)->limit(1);
		});

		// simple with alias which is invalid and should not be set
		$this->assertQueryTranslation('delete from `db`.`test` where `id` = ? limit 1', array(1), function($q) 
		{
			return $q->table('db.test as test')->delete()->where('id', 1)->limit(1);
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testDrop()
	{
		// simple
		$this->assertQueryTranslation('drop table `test`;', array(), function($q) 
		{
			return $q->table('test')->drop();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testTruncate()
	{
		// simple
		$this->assertQueryTranslation('truncate table `test`;', array(), function($q) 
		{
			return $q->table('test')->truncate();
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testCount()
	{
		$this->assertQueryExecution(array(array('count(*)' => 42)), 42, 'select count(*) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->count();
		});

		// with diffrent field
		$this->assertQueryExecution(array(array('count(`id`)' => 42)), 42, 'select count(`id`) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->count('id');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testSum()
	{
		$this->assertQueryExecution(array(array('sum(`views`)' => 123)), 123, 'select sum(`views`) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->sum('views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testMax()
	{
		$this->assertQueryExecution(array(array('max(`views`)' => 2343)), 2343, 'select max(`views`) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->max('views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testMin()
	{
		$this->assertQueryExecution(array(array('min(`views`)' => 12)), 12, 'select min(`views`) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->min('views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testAvg()
	{
		$this->assertQueryExecution(array(array('avg(`views`)' => 2342.324)), 2342.324, 'select avg(`views`) from `test` limit 0, 1', array(), function($q)
		{
			return $q->table('test')->select()->avg('views');
		});
	}

	/**
	 * mysql grammar tests
	 */
	public function testExists()
	{
		$this->assertQueryExecution(array(array('exists' => 1)), true, 'select exists(select * from `test`) as `exists`', array(), function($q)
		{
			return $q->table('test')->select()->exists();
		});

		// no results
		$this->assertQueryExecution(array(array('exists' => 0)), false, 'select exists(select * from `test`) as `exists`', array(), function($q)
		{
			return $q->table('test')->select()->exists();
		});

		// with some statements
		$this->assertQueryExecution(array(array('exists' => 0)), false, 'select exists(select * from `test` where ( `a` = ? or `c` = ? )) as `exists`', array('b', 'd'), function($q)
		{
			return $q->table('test')
				->select()
				->where(function( $q )
				{
					$q->where('a', 'b');
					$q->orWhere('c', 'd');
				})
				->exists();
		});
	}
}
