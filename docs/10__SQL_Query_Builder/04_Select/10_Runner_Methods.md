# SQL Select Runner Methods

One important thing to know here is that there are multiple so-called "runner methods". Think of them as helpers for common queries. These methods can modify your query, execute it and can do special operations with the returned result.

> Note: The displayed SQL query in the examples has no prepared statements. In other words the "?" have been replaced with the actual parameter.

## Fetching 

### Execute 

The absolute base runner method is `execute`. It's what all other runner methods are based on. 

It's an alias of `executeResultFetcher`, this means the method just forwards the plain data that you return inside your `ClanCats\Hydrahon\Builder` instance callback.

```php
$h->table('people')->select()->execute();
```

### Get 

The default runner method is the `get` method, it handles most of the built-in result modifications.

As an example the handling of an expected single result vs. collections.

```php
$people = $h->table('people');

$people->select()->where('name', 'Trevor')->execute(); // [[id: 1, name: 'Trevor']]
$people->select()->where('name', 'Trevor')->limit(1)->execute(); // [[id: 1, name: 'Trevor']]
```

Will return a collection or in other words an array of arrays. 

Using the `get` method Hydrahon knows that you are expecting a single result by setting the limit to 1.

```php
$people->select()->where('name', 'Trevor')->get(); // [[id: 1, name: 'Trevor']]
$people->select()->where('name', 'Trevor')->limit(1)->get(); // [id: 1, name: 'Trevor']
```

[~ PHPDoc](/src/Query/Sql/Select.php#get) 

### One

The method one hopefully speaks like a lot of methods for itself. It will add `limit 1` to your query and return a single result.

```php
$jeffry = $people->select()->where('name', 'jeffry')->one(); // [id: 2, name: 'jeffry']
```

[~ PHPDoc](/src/Query/Sql/Select.php#one) 

### First & Last

Selects the first/last result ordered by the given key (default is `id`).

```php
$firstPerson = $people->select()->first(); 
$lastPerson = $people->select()->last();
```

You can set on what key the first / last item should be selected:

```php
$youngest = $people->select()->first('age');
$oldest = $people->select()->last('age');
```

[~ PHPDoc](/src/Query/Sql/Select.php#first)

### Find 

Selects one item with the given value. This translates to a simple `where` with `limit` 1.

```php
// SQL: select * from `questions` where `id` = 42 limit 0, 1
$theAnswer = $h->table('questions')->select()->find(42);
```

You can also set the key.

```php
// SQL: select * from `people` where `name` = John limit 0, 1
$john = $people->select()->find('John', 'name');
```

[~ PHPDoc](/src/Query/Sql/Select.php#find)

### Column 

Select one specific value. 

```php
// SQL: select `age` from `people` where `name` = Ray limit 0, 1
$age = $people->select()->where('name', 'Ray')->column('age'); // returns 26
```

[~ PHPDoc](/src/Query/Sql/Select.php#column)

## Aggregators

### Count 

Selects using the MySQL `count` function and returns the number of results.

```php
// SQL: select count(*) from `people` limit 0, 1
$peopleCount = $people->select()->count();
```

By default the wildcard `*` is used but you can pass a field name:

```php
// SQL: select count(`deleted_at`) from `people` limit 0, 1
$deletedCount = $people->select()->count('deleted_at');
```

[~ PHPDoc](/src/Query/Sql/Select.php#count)

### Sum 

Selects using the MySQL `sum` function and returns the result.

```php
// SQL: select sum(`number_of_visits`) from `people` limit 0, 1
$totalVisits = $people->select()->sum('number_of_visits');
```

[~ PHPDoc](/src/Query/Sql/Select.php#sum)

### Min 

Selects using the MySQL `min` function and returns the result.

```php
// SQL: select min(`score`) from `game`.`ranking` limit 0, 1
$lowestScore = $h->table('game.ranking')->select()->min('score');
```

[~ PHPDoc](/src/Query/Sql/Select.php#min)

### Max 

Selects using the MySQL `max` function and returns the result.

```php
// SQL: select max(`score`) from `game`.`ranking` limit 0, 1
$highestScore = $h->table('game.ranking')->select()->max('score');
```

[~ PHPDoc](/src/Query/Sql/Select.php#max)

### Avarage 

Selects using the MySQL `avg` function and returns the result.

```php
// SQL: select avg(`age`) from `people` limit 0, 1
$avarageAge = $people->select()->avg('age');
```

[~ PHPDoc](/src/Query/Sql/Select.php#avg)

### Exists 

Returns a bool value if anything under the queries conditions exists.

```php
// SQL: select exists(select * from `people` where `age` > 89) as `exists
$hasOldPeople = $people->select()->where('age', '>', '89')->exists();
```

[~ PHPDoc](/src/Query/Sql/Select.php#exists)


