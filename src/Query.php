<?php namespace ClanCats\Hydrahon;

/**
 * Query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class Query
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
     * Construct a new builder with given nodes
     *
     * @param array                 $nodes
     * @return void
     */
    public function __construct( array $nodes )
    {
        
    }
}