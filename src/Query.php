<?php namespace ClanCats\Hydrahon;

/**
 * Query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

abstract class Query
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
     * Set the query table and optinal the database seperated by a dott
     * 
     * @throws ClanCats\Hydrahon\Exception
     * 
     * @param string            $table
     * @return self
     */
    public function table($table) 
    {
        if (strpos($table, '.') === false)
        {
            $this->table = $table; return $this;
        }

        $selection = explode('.', $table);

        if (count($selection) !== 2))
        {
            throw new Exception( 'Invalid argument given. You can only define one seperator.' );
        }

        list($this->database, $this->table) = $selection; return $this;
    }
}