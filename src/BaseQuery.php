<?php namespace ClanCats\Hydrahon;

/**
 * Base query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

abstract class BaseQuery
{   
    /**
     * The database the query should be executed on
     * 
     * @var string
     */
    protected $database = null;

    /**
     * The table the query should be executed on
     * 
     * @var string
     */
    protected $table = null;

    /**
     * The callback where we fetch the results from
     *
     * @var callable
     */
    protected $resultFetcher = null;

    /**
     * Construct new query object
     *
     * @param callable              $resultFetcher
     * @param string                $table
     * @param string                $database
     * @return void
     */
    public function __construct($resultFetcher, $table, $database = null)
    {
        $this->resultFetcher = $resultFetcher;
        $this->table = $table;
        $this->database = $database;
    }

    /**
     * Creates another query instance with the current parameters
     *
     * @param string                            $className
     * @return ClanCats\Hydrahon\BaseQuery
     */
    protected function createSubQuery($className)
    {
        return new $className($this->resultFetcher, $this->table, $this->database);
    }
}