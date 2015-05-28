<?php namespace ClanCats\Hydrahon;

/**
 * Query Builder manager
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

class Builder
{   
    /**
     * Array of available query grammers
     * 
     * @var array
     */
    protected static $grammar = array(

        // MySQL
        'mysql' => array(
            'ClanCats\\Hydrahon\\Query\\Sql',
            'ClanCats\\Hydrahon\\Translator\\Mysql',
        ),

        // SQLite
        'sqlite' => array(
            'ClanCats\\Hydrahon\\Query\\Sql',
            'ClanCats\\Hydrahon\\Translator\\Sqlite',
        ),

        // MongoDB
        'mongo' => array(
            'ClanCats\\Hydrahon\\Query\\MongoDB',
            'ClanCats\\Hydrahon\\Translator\\MongoDB',
        ),
    );

    /**
     * Extend the query builder by a new grammar
     * 
     * @throws ClanCats\Hydrahon\Exception
     * 
     * @param string                $grammerKey
     * @param string                $queryBuilder
     * @param string                $queryTranslator
     * @return void
     */
    public static function extend( $grammerKey, $queryBuilder, $queryTranslator )
    {
        if ( isset( static::$grammar[$grammerKey] ) )
        {
            throw new Exception( 'Cannot overwrite Hydrahon grammer.' );
        }

        static::$grammar[$grammerKey] = array( $queryBuilder, $queryTranslator );
    }

    /**
     * Currently loaded query builder
     * 
     * @var ClanCats\Hydrahon\Query
     */
    protected $queryBuilder = null;

    /**
     * Currently loaded query translator
     * 
     * @var ClanCats\Hydrahon\TranslatorInterface
     */
    protected $queryTranslator = null;

    /**
     * Create a new Hydrahon builder instance using the giving grammar
     *
     * @throws ClanCats\Hydrahon\Exception
     * 
     * @param string                $grammerKey
     * @return void
     */
    public function __construct( $grammerKey )
    {
        if ( isset( $this->grammar[$grammerKey] ) )
        {
            throw new Exception( 'There is no Hydrahon grammer "'.$grammerKey.'" registered.' );
        }

        
    }

    
}