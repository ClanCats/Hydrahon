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
