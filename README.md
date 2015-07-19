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

#### Select 

In our example we are going to execute multiple operations on the same table so instead of loading the table again and again we store it in a variable.

```php
$users = $h->table('users');
```

Also the examples don't show the `run` mehtod which has to be executed to obviously run the query.

```php
$users->select('name')->where('age', '>' 18)->run();
```

##### Basics 

Selecting everything

```php
$users->select()
```
```mysql
select * from `users`
```

Select some special fields. Hydrahon parses your input, that allows you to use the query the way your comfortable with it.

```php
$users->select(['name', 'age'])
// or
$users->select('name, age')
```
```mysql
select `name`, `age` from `users`
```

Of course you can alias fields, you can define them as array keys or with the as token.

```php
$users->select(['name', 'age', 'created_at' => 'c'])
// or
$users->select(['name', 'age', 'created_at as c'])
```
```mysql
select `name`, `age`, `created_at` as `c` from `users`
```

Sometimes you might have a special case that hydrahon does not cover natively. For such cases you can make use of raw expressions, those wont get parsed or escaped.

```php
$users->select([$users->raw("max('age')")])
```
```mysql
select max('age') from `users`
```
