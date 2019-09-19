# SQL Select Basics

The basics are `where`, `group`, `order` and `limit`. For joins check out [Joining data](docs://sql-query-builder/select/joining-data).

Keep in mind that I'm also aliasing the `people` table inside the `$people` variable for all examples in this document.

```php
$people = $h->table('people');
```

> Note: The displayed SQL query in the examples has no prepared statements. In other words the "?" placeholders have been replaced with the actual parameter.

## Columns / fields

By default, Hydrahon will simply select all fields using the `*` asterisks.

```php
// SQL: select * from `people`
$people->select()->get();
```

You can pass an array of column/field names as an argument to the `select` method.

```php
// SQL: select `name`, `age` from `people`
$people->select(['name', 'age'])->get();
```

Aliasing a field works by writing `as` just as you are used to from SQL.

```php
// SQL: select `name`, `some_way_to_long_column_name` as `col` from `people`
$people->select(['name', 'some_way_to_long_column_name as col'])->get();
```

> Note: that the column names are escaped for more info about that read: [parameter parsing and escaping](docs://introduction/parameter-parsing-escaping).

The query builder will also accept key value data and will convert them to an alias.

```php
// SQL: select `name`, `some_way_to_long_column_name` as `col` from `people`
$people->select(['name', 'some_way_to_long_column_name' => 'col'])->get();
```

You can overwrite the initial (all) fields/columns any time using the `fields` method.

```php
// SQL: select `name`, `group` from `people`
$people->select('id')->fields(['name', 'group'])->get();
```

### Adding fields

If you don't want to overwrite the selected fields you can make use of the `addField` method which will append the new field.

```php
// SQL: select `name`, `age` from `people`
$query = $people->select('name');

if ($iNeedTheAge) {
    $query->addField('age');
}
```

### Conditions / Aggregations / Raw

Because by default the columns will be quoted, you need to specify when you want to make some kind of raw operation or call an aggregation function.

```php
// SQL: select `name`, deleted_at is not null as is_deleted from `people`
use ClanCats\Hydrahon\Query\Expression as Ex;

$people->select([
    'name',
    new Ex('deleted_at is not null as is_deleted')
])->get();
```

This also works using the `addField` method. There you can pass the alias name a second argument.

```php
// SQL: select `name`, deleted_at is not null as `is_deleted` from `people`
use ClanCats\Hydrahon\Query\Expression as Ex;

$people->select('name')
    ->addField(new Ex('deleted_at is not null'), 'is_deleted')
    ->get();
```

Using the `Func` object you can still make use of Hydrahons escaping functionality.

```php
// SQL: select count(`people`.`group_id`) from `people`
use ClanCats\Hydrahon\Query\Sql\Func as F;

$people->select(new F('count', 'people.group_id'))->get();
```

## Where condition

A where **equals** condition is build like this:

```php
// SQL: select * from `people` where `name` = James
$people->select()->where('name', 'James')->get(); 
```

If you need to use a different operator just pass it as the second argument.

```php
// SQL: select * from `people` where `age` > 18
$people->select()->where('age', '>', '18')->get(); 
```

Passing an array as value will comma separate the values:

```php
// SQL: select * from `people` where `city` in (Zürich, Bern, Basel)
$people->select()->where('city', 'in', ['Zürich', 'Bern', 'Basel'])->get(); 
```

You can also use the the `whereIn` method which will do exactly the same thing.

```php
// SQL: select * from `people` where `city` in (Zürich, Bern, Basel)
$people->select()->whereIn('city', ['Zürich', 'Bern', 'Basel'])->get(); 
```

Or 

```php
// SQL: select * from `people` where `city` not in (Zürich, Bern, Basel)
$people->select()->whereNotIn('city', ['Zürich', 'Bern', 'Basel'])->get(); 
```

> Warning: When passing an empty array to `whereIn` or `whereNotIn` the condition will be simply skipped.

[~ PHPDoc](/src/Query/Sql/SelectBase.php#where) 

### Chaining conditions

By default using `where` will add the conditions using the logical `and` operator:

```php
// SQL: select * from `people` where `age` > 18 and `city` = Zürich
$people->select()
    ->where('age', '>', '18')
    ->where('city', 'Zürich')
    ->get(); 
```

use can use the methods `orWhere` and `andWhere` to specify which logical operator you want to use.

```php
// SQL: select * from `people` where `city` = Zürich or `city` = Bern
$people->select()
    ->where('city', 'Zürich')
    ->orWhere('city', 'Bern')
    ->get(); 
```

### Where NULL

Sometimes you need to know where something is nothing. 

```php
// SQL: select * from `people` where `deleted_at` is NULL
$people->select()->whereNull('deleted_at')->get();
```

Or reverse where it is not nothing.

```php
// SQL: select * from `people` where `deleted_at` is not NULL
$people->select()->whereNotNull('deleted_at')->get();
```

And of course, this also works with a or operator between the conditions.

```php
// SQL: 
//   select * from `people` 
//   where `last_login` > 1502276478 
//   and `deleted_at` is NULL 
//   or `is_admin_since` is not NULL
$people->select()
    ->where('last_login', '>', time() - 86400)
    ->whereNull('deleted_at')
    ->orWhereNotNull('is_admin_since')
    ->get();
```

### Grouped Where

Sometimes things get more complicated and you need to group some conditions together, we can do that by passing a function to the `where` method.

```php
// SQL: 
//   select * from `people` 
//   where `is_admin` = 1 
//   or ( 
//      `is_active` = 1 
//      and `deleted_at` is NULL 
//      and `email_confirmed_at` is not NULL 
//   )
$people->select()
    ->where('is_admin', 1)
    ->orWhere(function($q) 
    {
        $q->where('is_active', 1);
        $q->whereNull('deleted_at');
        $q->whereNotNull('email_confirmed_at');
    })
    ->get();
```

There is no layer limit so you can but Closure into Closure.

```php
// SQL: select * from `people` where `is_admin` = 1 or ( ( `is_active` = 1 and `is_moderator` = 1 ) or ( `is_active` = 1 and `deleted_at` is NULL and `email_confirmed_at` is not NULL ) )
$people->select()
    ->where('is_admin', 1)
    ->orWhere(function($q) 
    {
        $q->where(function($q) 
        {
            $q->where('is_active', 1);
            $q->where('is_moderator', 1);
        });
        $q->orWhere(function($q)
        {
            $q->where('is_active', 1);
            $q->whereNull('deleted_at');
            $q->whereNotNull('email_confirmed_at');
        });
    })
    ->get();
```

### Reset Where

If you find yourself in a situation where you just need a clean start you can reset all `where` conditions any time:

```php
$mySelectQuery->resetWheres();
```

## Grouping / Group By

Adding a group by statements is fairly simple.

```php
// SQL: select * from `people` group by `age`
$people->select()->groupBy('age')->get();
```

Or with multiple groups:

```php
// SQL: select * from `people` group by `age`, `is_active`
$people->select()->groupBy(['age', 'is_active'])->get();
```

[~ PHPDoc](/src/Query/Sql/Select.php#groupBy)

## Ordering / Order By

Also nothing overwhelming here. By default, the order direction is `asc`.

Order by single column:

```php
// SQL: select * from `people` order by `created` asc
$people->select()->orderBy('created')->get();
```

Change the direction:

```php
// SQL: select * from `people` order by `created` desc
$people->select()->orderBy('created', 'desc')->get();
```

Order by will also accept custom expressions.

```php
// SQL: select * from `cars` order by brand <> bmw desc
use ClanCats\Hydrahon\Query\Expression as Ex;

$cars->select()->orderBy(new Ex('brand <> bmw'), 'desc')->get();
```

### Multiple columns

Sort on multiple fields:

```php
// SQL: select * from `people` order by `created` asc, `firstname` asc
$people->select()->orderBy(['created', 'firstname'])->get();
```

Sort on multiple fields with different directions:


```php
// SQL: select * from `people` order by `lastname` asc, `firstname` asc, `score` desc
$people->select()->orderBy([
    'lastname' => 'asc',
    'firstname' => 'asc',
    'score' => 'desc',
])->get();
```

[~ PHPDoc](/src/Query/Sql/Select.php#orderBy)

## Limit / Offset

Because fetching trillions of records from the database makes your app probably crash we need to be able to limit queries.

### Limit

To set the limit use the method with that exact name:

```php
// SQL: select * from `people` limit 0, 10
$people->select()->limit(10)->get();
```

Using the exact same method you can also set the offset. So when two arguments are given the second one acts as limit and the first one as the offset. This might seem confusing but I wanted to stay as close as possible to SQL.

```php
// with offset 100
// SQL: select * from `people` limit 100, 10
$people->select()->limit(100, 10)->get();
```

[~ PHPDoc](/src/Query/Sql/SelectBase.php#limit)

### Offset

If you like things a little bit more expressive you can also use the offset method.

```php
// SQL: select * from `people` limit 100, 10
$people->select()->limit(10)->offset(100)->get();
```

[~ PHPDoc](/src/Query/Sql/SelectBase.php#offset)

### Page

The `page` method is just a helper that also sets a limit and offset. **The default page size is 25.**

```php
// SQL: select * from `people` limit 0, 25
$people->select()->page(0)->get();

// SQL: select * from `people` limit 125, 25
$people->select()->page(5)->get();

// SQL: select * from `people` limit 250, 50
$people->select()->page(5, 50)->get();
```

> Note: Pages always start at **0**!

[~ PHPDoc](/src/Query/Sql/SelectBase.php#page)

## Distinct select

To make your select distinct simply call the method corresponding method.

```php
// SQL: select distinct * from `people`
$people->select()->distinct();
```

[~ PHPDoc](/src/Query/Sql/Select.php#distinct)
