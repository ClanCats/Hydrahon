# MySQL PDO query builder implementation

This is copy pasta from the README.

```php 
$connection = new PDO('mysql:host=localhost;dbname=my_database', 'username', 'password');

// create a new mysql query builder
$h = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
{
    $statement = $connection->prepare($queryString);
    $statement->execute($queryParameters);

    // when the query is fetchable return all results and let hydrahon do the rest
    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
    {
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
});
```


