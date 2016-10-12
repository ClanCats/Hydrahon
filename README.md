# Hydrahon

![Hydrahon banner](https://cloud.githubusercontent.com/assets/956212/7947360/e36d75ea-097c-11e5-89c0-be7b56bbf5ca.png)

Hydrahon is a query builder, and only a query builder. It does not contain a PDO wrapper or anything. It's built to add query building into existing systems without implementing an entire new Database layer.

[![Build Status](https://travis-ci.org/ClanCats/Hydrahon.svg?branch=master)](https://travis-ci.org/ClanCats/Hydrahon)
[![Packagist](https://img.shields.io/packagist/dt/clancats/hydrahon.svg)](https://packagist.org/packages/clancats/hydrahon)
[![Packagist](https://img.shields.io/packagist/l/clancats/hydrahon.svg)]()
[![GitHub release](https://img.shields.io/github/release/clancats/hydrahon.svg)](https://github.com/ClanCats/Hydrahon/releases)


## Installation

Hydrahon follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require 'clancats/hydrahon:dev-master'
```

## Usage MySQL

### Create a builder

Again Hydrahon is **not** built as a database library, it's just a query builder. In this example, I'm going to present you an easy example of a PDO mysql implementation.

```php 
$connection = new PDO('mysql:host=localhost;dbname=my_database', 'username', 'password');

$hydrahon = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
{
    $statement = $connection->prepare($queryString);
    $statement->execute($queryParameters);

    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
    {
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
});
```

---

### Structure 

 * [Basics](#basics)
 * [Select](#select)
   * [Runners](#runners)
   * [Basics](#basics)
   * [Where](#where)
   * [Order By](#ordering)
   * [Join](#joins)
   * [Limit and Offset](#limit-offset-and-page)

---

> Note: Please note that in the following examples the variable `$h` contains a Hydrahon query builder instance.

---

### Basics

Lets start with a super basic example:

#### Inserting:

```php
$h->table('people')->insert(
[
    ['name' => 'Ray', 'age' => 25],
    ['name' => 'John', 'age' => 30],
    ['name' => 'Ali', 'age' => 22],
])->execute();
```

#### Updating:

```php
$h->table('people')->update()
    ->set('age', 26)
    ->where('name', 'Ray')
    ->execute();
```

#### Deleting:

```php
$h->table('people')->delete()
    ->where('name', 'John')
    ->execute();
```

#### Selecting:

```php
$h->table('people')->select()
    ->get();
```

---

### SQL Select 

In our example we are going to execute multiple operations on the same table, so instead of loading the table over and over again we store it in a variable.

```php
$users = $h->table('users');
```

#### Runners 

The runner methods execute your query and return a result. There are many diffrent runner methods and each one acts like an helper. This means a runner method can modifiy your query and the result.

##### "Execute" method

The `execute` method is an alias of `executeResultFetcher`, this means the method just forwards the plain data that you return inside your `ClanCats\Hydrahon\Builder` instance callback.

```php
$users->select()->limit(10)->execute();
```

#### "Get" method

The default runner method is the `get` method which can do some operations on your data.

```php
$users->select(['name'])->where('age', '>', 22)->get();
```

For example by setting the limit of your query to _one_, you will also receive just that one single result. (Not an array of results). 

```php
$users->select()->get(); // returns: array(array(name: joe))
$users->select()->limit(1)->get(); // returns: array(name: joe)
```

#### "One" method

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

You can also pass a different key.

```php
$users->select()->first('created_at');
```

**count results**

This special guy returns you the count of the current query:

```php
$users->select()->where('age', '>', 18)->count();
```

**single column result**

Sometimes you just need one value, for that we have the column function

```php
$users->select()->where('name', 'johanna')->column('age');
```

#### Basics 

Selecting everything

```php
$users->select()
```
```sql
select * from `users`
```

Select some special fields. Hydrahon parses your input, that allows you to use the query builder the way you are comfortable with.

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

Sometimes you might have a special case that hydrahon does not cover natively. For such cases you can make use of raw expressions, those will not get parsed or escaped.

```php
$users->select([$users->raw("max('age')")])
```
```sql
select max('age') from `users`
```

#### Where
The `where` statement does not only apply to the `select` query, but also to update and `delete`.

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

Of course there is also an or where statement.

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

#### Ordering

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

Again, there are several ways you can do this, my philosophy is to give you as much freedom as possible.

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

When passing an array, you can also define the direction as array value.

```php
$users->select()->orderBy(['name', 'created_at' => 'desc']);
```
```sql
select * from `users` order by `name` asc, `created_at` desc
```	

#### Joins

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

The default join type is `left`, for every join type there is its own method.

 * `leftJoin`
 * `rightJoin`
 * `innerJoin`
 * `outterJoin`

#### Limit, Offset and Page

When setting the limit to just one entry, you will receive it as a single result and not as result collection.

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
