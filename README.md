# Hydrahon

Hydrahon is a query builder, and only a query builder. It does not contain a PDO wrapper or something to actually execute theses queries. I'ts build to implement query building into existing systems where you can't start implmenting an entire new DB system.



```php
// ignore this...

$hydrahon = new ClanCats\Hydrahon\Builder('mysql', function( $query, $translated )
{
	// this is the callback function everytime executed when
	// a query is run here you can implement your logic
	list( $queryString, $queryParameters ) = $translated;

	echo "MySQL query: ".$queryString." with parameters: ".print_r( $queryParameters, true );
});

$hydrahon->notes->insert( ['title' => 'Hello'] );

$hydrahon->notes->select(['id','title'])->where( 'title', 'like', 'H%' )->get();

$hydrahon->table('other_db.notes')->find(1);

$hydrahon->table('notes')->delete()->where('created', '<', time() - 60 * 60 * 24 );
```