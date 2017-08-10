# SQL Select Basics

The basics are `where`, `order`, `group` and `limit` conditions. For joins check out [Joining data](docs://sql-query-builder/select/joining-data).

Keep in mind that im also aliasing the `people` table inside the `$people` variable for the following examples.

```php
$people = $h->table('people');
```

> Note: The displayed SQL query in the examples has no prepared statements. In other words the "?" have been replaced with the actual parameter.

## Columns / fields

By default hydrahon will simply select all fields using the `*` astericks.

```php
// SQL: select * from `people`
$people->select()->get();
```

You can pass an array of column / field names as argument to the `select`.

```php
// SQL: select `name`, `age` from `people`
$people->select(['name', 'age'])->get();
```

Aliasing a field works by writing `as`.

```php
// SQL: select `name`, `some_way_to_long_column_name` as `col` from `people`
$people->select(['name', 'some_way_to_long_column_name as col'])->get();
```

> Note: that the colum names are escaped for more infos about that read: [parameter parsing and escaping](docs://introduction/parameter-parsing-escaping).

You can overwrite the inital fields / columns any time using the `field` method.

```php
// SQL: select `name`, `group` from `people`
$people->select()->fields(['name', 'group'])->get();
```

### Adding fields

Also using the `addField` method you can add additional fields any time.

```php
$query = $people->select('name');

if ($iNeedTheAge) {
    $query->addField('age');
}
```

### Conditions / Aggregations / Raw

Because by default the columns will be quoted, you need to specify when you want to make some kind of raw operation or call a aggregation function.

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

A where equals condition is build like this:

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

> Warning: When passing an empty array to `whereIn` the condition will be simply skipped.

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

And of course this also works with a or operator between the conditions.

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