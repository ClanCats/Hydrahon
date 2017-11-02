# SQL insert 

> Note: The displayed SQL query in the examples has no prepared statements. In other words the "?" placeholders have been replaced with the actual parameter.

## Inserting data

```php
// SQL: insert into `people` (`firstname`, `lastname`) values ('Ethan', 'Klein')
$h->table('people')->insert(['firstname' => 'Ethan', 'lastname' => 'Klein'])->execute();
```

## Bulk inserting data

When you want to insert multiple rows at once simply pass in a multidimensional array.

```php
// SQL: insert into `people` (`firstname`, `lastname`) values ('Ethan', 'Klein'), ('Hila,' 'Klein')
$h->table('people')->insert([
    ['firstname' => 'Ethan', 'lastname' => 'Klein'],
    ['firstname' => 'Hila', 'lastname' => 'Klein'],
])->execute();
```

## Values

The first argument of the `insert` method simply forwards the given data to the `values` method.

The `values` method will always append the given data so you write stuff like this which will generate the exact same query as above.

```php
// SQL: insert into `people` (`firstname`, `lastname`) values ('Ethan', 'Klein'), ('Hila,' 'Klein')
$insert = $h->table('people')->insert();

$insert->values(['firstname' => 'Ethan', 'lastname' => 'Klein']);
$insert->values(['firstname' => 'Hila', 'lastname' => 'Klein']);

$insert->execute();
```

[~ PHPDoc](/src/Query/Sql/Insert.php#values)

## Ignore

You can toggle insert ignore.

```php
// SQL: insert ignore into `people` (`firstname`, `lastname`) values (Ethan, Klein)
$h->table('people')
    ->insert()
    ->values(['firstname' => 'Ethan', 'lastname' => 'Klein'])
    ->ignore()
    ->execute();
```

[~ PHPDoc](/src/Query/Sql/Insert.php#ignore)

## Reset values

Because values are always appended you need to be able to reset them at some point. For that, we have the `resetValues` method.

```php
// SQL: insert into `people` (`firstname`, `lastname`) values (Hila, Klein)
$insert = $h->table('people')->insert();

$insert->values(['firstname' => 'Ethan', 'lastname' => 'Klein']);
$insert->resetValues();
$insert->values(['firstname' => 'Hila', 'lastname' => 'Klein']);

$insert->execute();
```

[~ PHPDoc](/src/Query/Sql/Insert.php#resetValues)