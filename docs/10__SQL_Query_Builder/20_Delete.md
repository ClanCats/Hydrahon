# SQL Delete 

The `delete` query builder also extends the select base. This gives you access to all `where` and `limit` methods. Besides that, the delete query builder has no special methods.

So take a look at the [select documentation](docs://sql-query-builder/select/basics).

## Deleting data

Delete the user with the id 1.

```php
// SQL: delete from `users` where `id` = 1
$h->table('users')->delete()->where('id', 1)->execute();
```

Delete 10 inactive users.

```php
// SQL: delete from `users` where `active` = 0 limit 10
$h->table('users')->delete()->where('active', 0)->limit(10)->execute();
```
