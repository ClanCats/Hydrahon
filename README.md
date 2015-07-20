# Hydrahon

![Hydrahon banner](https://cloud.githubusercontent.com/assets/956212/7947360/e36d75ea-097c-11e5-89c0-be7b56bbf5ca.png)

Hydrahon is a query builder, and only a query builder. It does not contain a PDO wrapper or something. I'ts build to add query building into existing systems without implementing an entire new Database layer.

[![Build Status](https://travis-ci.org/ClanCats/Hydrahon.svg?branch=master)](https://travis-ci.org/ClanCats/Hydrahon)
[![Packagist](https://img.shields.io/packagist/dt/clancats/hydrahon.svg)](https://packagist.org/packages/clancats/hydrahon)
[![Packagist](https://img.shields.io/packagist/l/clancats/hydrahon.svg)]()
[![GitHub release](https://img.shields.io/github/release/clancats/hydrahon.svg)](https://github.com/ClanCats/Hydrahon/releases)

## Status

**This library is still in work.**

 - [x] SQL query structure
 - [x] SQL select query builder
 - [x] Mysql select query translator
 - [x] SQL insert query builder and translator
 - [x] SQL update query builder and translator
 - [x] SQL delete query builder and translator
 - [ ] Port more selection result helpers
 - [ ] Clean up translation unit tests. 

## Installation

Hydrahon follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require 'clancats/hydrahon:dev-master'
```

## Usage

### Creating hydrahon builder

Again Hydrahon is **not** build as a database library, it's just a query builder. In this example im going to show you a dead simple PDO mysql implementation.

```php 
$connection = new PDO('mysql:host=localhost;dbname=my_database', 'username', 'password');

$hydrahon = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
{
    $statement = $connection->prepare($queryString);
    $statement->execute($queryParameters);

    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\Select)
    {
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
});
```

### SQL query builder

Please note that in the following examples the variable `$h` contains a Hydrahon query builder instance.

 * [Select](#select)
   * [Runners](#runners)
   * [Basics](#basics)
   * [Where](#where)
   * [Order By](#ordering)
   * [Join](#joins)
   * [Limit and Offset](#limit-offset-and-page)

#### Select 

In our example we are going to execute multiple operations on the same table so instead of loading the table again and again we store it in a variable.

```php
$users = $h->table('users');
```

##### Runners 

Also the examples don't show the `run` mehtod which has to be executed to obviously run the query.

```php
$users->select('name')->where('age', '>', 18)->run();
```

There are also other runners to cover common use cases.

**single result**

Instead of retrieving an array of results you can direclty access a single one.

```php
$users->select()->where('name', 'jeffry')->one();
```

**first and last result**

Returns the first result of table orderd by the default key `id`.

```php
$users->select()->first();
// or 
$users->select()->last();
```

You can also pass a diffrent key

```php
$users->select()->first('created_at');
```

**count results**

This special guy returns you the count of the current query:

```php
$users->select()->where('age', '>', 18)->count();
```

**single column result**

Sometimes you just need one value for that we have the column function

```php
$users->select()->where('name', 'johanna')->column('age');
```

##### Basics 

Selecting everything

```php
$users->select()
```
```sql
select * from `users`
```

Select some special fields. Hydrahon parses your input, that allows you to use the query the way your comfortable with it.

```php
$users->select(['name', 'age'])
// or
$users->select('name, age')
```
```sql
select `name`, `age` from `users`
```

Of course you can alias fields, you can define them as array keys or with the as token.

```php
$users->select(['name', 'age', 'created_at' => 'c'])
// or
$users->select(['name', 'age', 'created_at as c'])
```
```sql
select `name`, `age`, `created_at` as `c` from `users`
```

Sometimes you might have a special case that hydrahon does not cover natively. For such cases you can make use of raw expressions, those wont get parsed or escaped.

```php
$users->select([$users->raw("max('age')")])
```
```sql
select max('age') from `users`
```

##### Where

The `where` statement does not only apply to the `select` query also to `update` and `delete`.

```php
$users->select()->where('active', 1)
```
```sql
select * from `users` where `active` = ?
```
You might wonder why there is an `?` in the query. The given `1` gets automatically passed as prepared parameter to avoid sql injection.

Setting multiple where statements will result in an `and` statement.

```php
$users->select()->where('active', 1)->where('age', '>', 18)
```
```sql
select * from `users` where `active` = ? and `age` > ?
```	

**or?**

Of course there is also a or where statement.

```php
$users->select()->where('active', 1)->orWhere('admin', 1)
```
```sql
select * from `users` where `active` = ? or `admin` = ?
```	

**Scopes**

You can scope wheres by using callbacks.

```php
$users->select()
	->where('age', '>', 18)
	->where(function($q) {
		$q->where('active', 1)->orWhere('admin', 1);
	});
```
```sql
select * from `users` where `age` > ? and ( `active` = ? or `admin` = ? )
```	

**in array**

Arrays can also be passed as where parameters.

```php
$users->select()->where('id', 'in', [213, 32, 53, 43]);
```
```sql
select * from `users` where `id` in (?, ?, ?, ?)
```	

##### Ordering

```php
$users->select()->orderBy('name');
```
```sql
select * from `users` order by `name` asc
```	

Setting the order direction.

```php
$users->select()->orderBy('name', 'desc');
```
```sql
select * from `users` order by `name` desc
```	

**Ordering with multiple keys**

Again there are several ways how to do this, my philosphy is to allow as mutch freedom as possible.

```php
$users->select()->orderBy('name, created_at');
// or 
$users->select()->orderBy(['name', 'created_at']);
// or 
$users->select()->orderBy('name')->orderBy('created_at');
```
```sql
select * from `users` order by `name` desc, `created_at` asc
```	

When passing an array you can also define the direction as array value.

```php
$users->select()->orderBy(['name', 'created_at' => 'desc']);
```
```sql
select * from `users` order by `name` asc, `created_at` desc
```	

##### Joins

The automatic escaping becomes really handy when working with multiple tables.

```php
$users->select(['users.name', 'img.url'])
	->join('user_images as img', 'users.id', '=', 'img.user_id')
	->where('img.active', 1)
```

```sql
select `users`.`name`, `img`.`url` 
	from `users` 
	left join `user_images` as `img` on `users`.`id` = `img`.`user_id` 
	where `img`.`active` = ?
```

The default join type is `left`, for every join type there is an own method.

 * `leftJoin`
 * `rightJoin`
 * `innerJoin`
 * `outterJoin`

##### Limit, Offset and Page

When setting the limit to just one entry you will receive your single result and not an array of them.

```php
$users->select()->limit(1); // returns single result
```
```sql
select * from `users` limit 0, 1
```

```php
$users->select()->limit(2); // returns an array of results.
```
```sql
select * from `users` limit 0, 2
```

with offset:

```php
$users->select()->limit( 25, 10 );
```
```sql
select * from `users` limit 25, 10
```

simple paging:

```php
users->select()->page(0);
```
```sql
select * from `users` limit 0, 25
```

The default page size is 25 entries.

```php
users->select()->page(3, 15);
```
```sql
select * from `users` limit 45, 15
```
