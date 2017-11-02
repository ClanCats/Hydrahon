# Parameter parsing and escaping

One important thing to know is that Hydrahon will try to parse and auto escapes your parameters. Read this carefully to avoid unexpected behavior.

> Warning: First of all, hydrahons escaping does **NOT** prevent SQL injection! Prepared statements will do that job for you. Hydrahon escapes keys & names to avoid a collision with a reserved keywords.

## Example

For example when we define the following select query.

```php 
$h->table('users')->select('id, name, password');
```

And dump the query object:

```
class ClanCats\Hydrahon\Query\Sql\Select#9 (14) {
  protected $fields =>
  array(3) {
    [0] => array(2) {
      [0] => string(2) "id" 
      [1] => NULL
    }
    [1] => array(2) {
      [0] => string(4) "name" 
      [1] => NULL
    }
    [2] => array(2) {
      [0] => string(8) "password" 
      [1] => NULL
    }
  }
```

We can see that Hydrahon parsed the given string and exploded it into separate values.

The same thing happens with the `as` keyword and when you add a database prefix.

```php
$h->table('users')->select('db.fullname as name');
```

But in this case, the escaping will happen by executing the query. This is the resulting SQL query:

```sql
select `db`.`fullname` as `name` from `users`
```

As you can see all field names have been quoted. This is done to prevent collisions with reserved SQL keywords.

