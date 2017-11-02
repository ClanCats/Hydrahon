# SQL Select Joins

Do you like to join things? We too!

## Basic Join

Let's assume we have two tables, a users table and a comments table. Every comment has a column containing a user id. Now we want to fetch all comment bodies including the name of their creators.

```php
// SQL: 
//   select `c`.`body`, `u`.`name` 
//   from `comments` as `c` 
//   left join `users` as `u` on `c`.`user_id` = `u`.`id`
$h->table('comments as c')
    ->select(['c.body', 'u.name'])
    ->join('users as u', 'c.user_id', '=', 'u.id')
    ->get();
```

the default method `join` will generate a **left** join.

[~ PHPDoc](/src/Query/Sql/Select.php#join)

### Right Join

[~ PHPDoc](/src/Query/Sql/Select.php#rightJoin)

### Inner Join

[~ PHPDoc](/src/Query/Sql/Select.php#innerJoin)

### Outer Join

[~ PHPDoc](/src/Query/Sql/Select.php#outerJoin)


## Complex Join

Sometimes you need to join data on more than one condition. Therefor you can pass a callback to the all `join` methods allowing you to specify more conditions.

```php
// SQL:
//   select `c`.`body`, `u`.`name` from `comments` as `c` 
//   inner join `users` as `u` 
//   on `u`.`id` = `c`.`user_id` 
//   or `u`.`id` = `c`.`moderator_id` 
//   and ( `u`.`active` = 1 and `u`.`deleted_at` is NULL )
$h->table('comments as c')
    ->select(['c.body', 'u.name'])
    ->innerJoin('users as u', function($join) 
    {
        $join->on('u.id', '=', 'c.user_id');
        $join->orOn('u.id', '=', 'c.moderator_id');
        $join->where(function($q) {
            $q->where('u.active', true);
            $q->whereNull('u.deleted_at');
        });
    })
    ->get();
```