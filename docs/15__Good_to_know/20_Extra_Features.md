### Manually constructed objects

Join conditions:

```php
// select * from `tbl1` left join `tbl2` on `tbl1`.`id` = `tbl2`.`id` and `tbl1`.`id` > ?
$on = (new ClanCats\Hydrahon\Query\Sql\SelectJoin)->on('tbl1.id','=','tbl2.id')->where('tbl1.id','>',10);
$q->table('tbl1')->select()->join('tbl2',$on)->execute();
```

Query objects for subselects:

```php
// select * from (select `f1`, `f2` from `tbl` where `f2` <> ? order by `f1` desc) as `t1` where `f1` = ?
$select = $q->table('tbl')->select('f1','f2')->orderBy('f1','desc')->where('f2','<>',15);
$q->table(['t1' => $select])->select()->where('f1','=',3)->execute();
```

### Variadic syntax for select fields:

```php
// select `id`, `name` from `users`
$q->table('users')->select('id','name')->execute();
$q->table('users')->select('id,name')->execute();
$q->table('users')->select(['id','name'])->execute();
```

### Special values:

Null values:

```php
// select `field` from `tbl` where `id` is null
$q->table('tbl')->select('field')->where('id','is',$q->value('null'))->execute();
```

Default values:

```php
// insert into `tbl` (`col1`) values (default)
$q->table('tbl')->insert(['col1' => $q->value('default')])->execute();
```

### Multiple tables in from clause:

```php
// select `t1`.`col`, `t2`.`col` from `t1` as `table_1`,`t2` as `table_2`
$q->table(['t1' => 'table_1', 't2' => 'table_2'])->select('t1.col,t2.col')->execute();
```

### Force an operand to be escaped as a field:

```php
// select `col` from `table` where `col` = `id`
$q->table('table')->select('col')->where('col','=',$q->field('id'))->execute();
```


