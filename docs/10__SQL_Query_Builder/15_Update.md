# SQL Update 

Updating data follows very similar rules as the select. At least when it comes to query building. 

> Note: The displayed SQL query in the examples has no prepared statements. In other words the "?" placeholders have been replaced with the actual parameter.

## Basic update 

```php
// SQL: update `users` set `active` = 0
$h->table('users')->update(['active' => 0])->execute();
```

The update query builder extends the select base, allowing you to make use of all `where` and `limit` methods.

So because you probably don't want to set all your user's inactive start some filtering.

```php
// SQL: update `users` set `active` = 0 where `last_login` < '2015-01-01'
$h->table('users')
    ->update(['active' => 0])
    ->where('last_login', '<', '2015-01-01')
    ->execute();

```

The argument that the `update` methods takes in will be forwarded to the `set` method.

[~ PHPDoc](/src/Query/Sql/Update.php#set)

The set method can be used in a key-value manner.

```php
// SQL: update `users` set `name` = Arthur, `follower_count` = 42 where `id` = 12
$h->table('users')->update()
    ->set('name', 'Arthur')
    ->set('follower_count', 42)
    ->where('id', 12)
    ->execute();
```

Or you might also pass in an array.

```php
// SQL: update `users` set `name` = Arthur, `follower_count` = 42 where `id` = 12
$h->table('users')->update()
    ->set(['name' => 'Arthur', 'follower_count' => 42)
    ->where('id', 12)
    ->execute();
```
