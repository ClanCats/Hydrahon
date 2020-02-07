<p align="center"><a href="http://clancats.io/hydrahon/master/" target="_blank">
    <img width="100px" src="https://user-images.githubusercontent.com/956212/28079683-a2d93bf8-6669-11e7-920e-5779b665a909.png">
</a></p>

# Hydrahon

Hydrahon is a **standalone** database / SQL query builder written in PHP. It was built to enhance existing frameworks, libraries and applications that handle the database connection on their own. It **does not** come with a **PDO** or **mysqli** wrapper. The naming is heavily inspired by Eloquent and the Kohana Framework Database component.

**What does that mean "Standalone query builder"?**

Hydrahon only generates a query **string** and an array of parameters. On its own, it is not able to execute a query.

[![Build Status](https://travis-ci.org/ClanCats/Hydrahon.svg?branch=master)](https://travis-ci.org/ClanCats/Hydrahon)
[![Packagist](https://img.shields.io/packagist/dt/clancats/hydrahon.svg)](https://packagist.org/packages/clancats/hydrahon)
[![Packagist](https://img.shields.io/packagist/l/clancats/hydrahon.svg)](https://github.com/ClanCats/Hydrahon/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/hydrahon.svg)](https://github.com/ClanCats/Hydrahon/releases)

## Status

* The Hydrahon **MySQL** query builder is stable and used in production.
* The Hydrahon **AQL** (Arango Query Language) query builder is currently in development.
* A builder for Elasticsearch is on my mind but not in development.

## Installation

Hydrahon follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/hydrahon
```
## Documentation 💡

The full documentation can be found on [clancats.io](http://clancats.io/hydrahon/master/)

## Quick Start (MySQL) ⚡️

Hydrahon is designed to be a pretty generic query builder. So for this quick start, we stick with SQL.

### Create a builder

Again this library is **not** built as a full database abstraction or ORM, it is only and will always be only a query builder. This means we need to implement the database connection and fetching by ourselves. The Hydrahon constructor therefore requires you to provide a callback function that does this, and returns the results.

In this example, we are going to use [PDO](http://php.net/manual/en/book.pdo.php)

```php 
$connection = new PDO('mysql:host=localhost;dbname=my_database;charset=utf8', 'username', 'password');

// create a new mysql query builder
$h = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
{
    $statement = $connection->prepare($queryString);
    $statement->execute($queryParameters);

    // when the query is fetchable return all results and let hydrahon do the rest
    // (there's no results to be fetched for an update-query for example)
    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
    {
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
});
```

And we are ready and set. The variable `$h` contains now a MySQL query builder.

### Setup a simple table

To continue with our examples, we need to create a simple MySQL table.

```sql
CREATE TABLE `people` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `age` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Inserting

Currently, we do not have any data, to fix this let's go and insert some.

```php
// In our example we are going to execute multiple operations on the same table, 
// so instead of loading the table over and over again, we store it in a variable.
$people = $h->table('people');

$people->insert(
[
    ['name' => 'Ray', 'age' => 25],
    ['name' => 'John',  'age' => 30],
    ['name' => 'Ali', 'age' => 22],
])->execute();
```

Will execute the following query:

```sql
insert into `people` (`age`, `name`) values (?, ?), (?, ?), (?, ?)
```

As you can see Hydrahon automatically escapes the parameters. 

However, because we are humans that get confused when there are hundreds of thousands of questions marks, I will continue to always display the runnable query:

```sql
insert into `people` (`age`, `name`) values (25, Ray), (30, John), (22, Ali)
```

### Updating

Ah snap, time runs so fast, "Ray" is actually already 26.

```php
$people->update()
    ->set('age', 26)
    ->where('name', 'Ray')
->execute();
```

Generating:

```sql
update `people` set `age` = 26 where `name` = 'Ray'
```

Currently, you might think: "Well isn't it much simpler to just write the SQL query? I mean the PHP code is even longer...". 

You have to understand that these are some very very basic examples the Hydrahon query builder starts to shine when things get more complex. However, a "Quick Start" is just the wrong place for complicated stuff, so throw an eye on the [full documentation](http://clancats.io/hydrahon/master/).

### Deleting 

Dammit John, I hate you...

```php
$people->delete()
    ->where('name', 'John')
->execute();
```

Generating:

```sql
delete from `people` where `name` = 'John'
```

### Selecting

And finally, fetch the data.

```php
$people->select()->get();
```

Generating:

```sql
select * from `people`
```

Result:

```json
[
  {
    "id": "1",
    "name": "Ray",
    "age": "26"
  },
  {
    "id": "3",
    "name": "Ali",
    "age": "22"
  }
]
```

Notice that we use `->get()` to actually fetch data, while we used `->execute()` for our previous queries (updates, inserts and deletes). See the full documentation for more information about the Hydrahon [runners methods](https://clancats.io/hydrahon/master/sql-query-builder/select/runner-methods).

### Where conditions

For the next few examples, lets assume a larger dataset so that the queries make sense.

Chaining where conditions:

```php
// select * from `people` where `age` = 21 and `name` like 'J%'
$people->select()
    ->where('age', 21)
    ->where('name', 'like', 'J%')
    ->get();
```

Notice how omitting the operator in the first condition `->where('age', 21)` makes Hydrahon default to `=`.

By default all where conditions are defined with the `and` operator.

Different where operators:

```php
// select * from `people` where `name` like 'J%' or `name` like 'I%'
$people->select()
    ->where('name', 'like', 'J%')
    ->orWhere('name', 'like', 'I%')
    ->get();
```

Please check the [relevant section in the full documentation](https://clancats.io/hydrahon/master/sql-query-builder/select/basics) for more where-functions, like
- `whereIn()`
- `whereNotIn()`
- `whereNull()`
- `whereNotNull()`

#### Where scopes

Allowing you to group conditions:

```php
// select * from `people` where ( `age` > 21 and `age` < 99 ) or `group` = admin
$people->select()
    ->where(function($q) 
    {
        $q->where('age', '>', 21);
        $q->where('age', '<', 99);
    })
    ->orWhere('group', 'admin')
    ->get();
```

### Joins

Joining tables:

```php
// select 
//     `people`.`name`, `groups`.`name` as `group_name` 
// from `people` 
// left join `groups` on `groups`.`id` = `people`.`group_id`
$people->select('people.name, groups.name as group_name')
    ->join('groups', 'groups.id', '=', 'people.group_id')
    ->get();
```

### Grouping

Grouping data:

```php
// select * from `people` group by `age`
$people->select()->groupBy('age')->get();
```

### Ordering

Ordering data:

```php
// select * from `people` order by `age` desc
$people->select()->orderBy('age', 'desc')->get();

// select * from `people` order by `age` desc, `name` asc
$people->select()->orderBy(['age' => 'desc', 'name' => 'asc'])->get();
```

### Limiting data

Limit and offset:

```php
// select * from `people` limit 0, 10
$people->select()->limit(10)->get();

// select * from `people` limit 100, 10
$people->select()->limit(100, 10)->get();

// select * from `people` limit 100, 10
$people->select()->limit(10)->offset(100)->get();

// select * from `people` limit 150, 30
$people->select()->page(5, 30)->get();
```

**Small reminder this is the quick start, check out the full docs.**

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Hydrahon/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Hydrahon/blob/master/LICENSE) for more information.
