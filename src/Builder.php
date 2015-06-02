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
     * Array of available query grammars
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
     * @param string                $grammarKey
     * @param string                $queryBuilder
     * @param string                $queryTranslator
     * @return void
     */
    public static function extend($grammarKey, $queryBuilder, $queryTranslator)
    {
        if (isset(static::$grammar[$grammarKey])) 
        {
            throw new Exception('Cannot overwrite Hydrahon grammar.');
        }

        static::$grammar[$grammarKey] = array($queryBuilder, $queryTranslator);
    }

    /**
     * The current query class
     *
     * @var string
     */
    protected $queryClass = null;

    /**
     * Currently loaded query translator
     *
     * @var ClanCats\Hydrahon\TranslatorInterface
     */
    protected $queryTranslator = null;

    /**
     * User given execution callback
     *
     * @var callable
     */
    protected $executionCallback = null;

    /**
     * Create a new Hydrahon builder instance using the giving grammar
     *
     * @throws ClanCats\Hydrahon\Exception
     *
     * @param string                $grammarKey
     * @param callable              $executionCallback
     * @return void
     */
    public function __construct($grammarKey, $executionCallback)
    {
        if (!isset(static::$grammar[$grammarKey])) 
        {
            throw new Exception('There is no Hydrahon grammar "' . $grammarKey . '" registered.');
        }

        if (!is_callable($executionCallback)) 
        {
            throw new Exception('Invalid query exec callback given.');
        }

        $this->executionCallback = $executionCallback;

        // prepare the current grammar
        list($this->queryClass, $translatorClass) = static::$grammar[$grammarKey];
        $this->queryTranslator = new $translatorClass;

        // check if the translator is valid
        if (!$this->queryTranslator instanceof TranslatorInterface)
        {
            throw new Exception('A query translator must implement the "TranslatorInterface" interface.');
        }
    }

    /**
     * Creates a new query object with the given table and database and
     * sets the query table and optinal the database seperated by a dott
     * 
     * @param string                        $table
     * @return ClanCats\Hydrahon\BaseQuery
     */
    public function table($table)
    {
        $database = null;

        if (strpos($table, '.') === false)
        {
            $table = $table;
        }
        else
        {
            $selection = explode('.', $table);

            if (count($selection) !== 2)
            {
                throw new Exception( 'Invalid argument given. You can only define one seperator.' );
            }

            list($database, $table) = $selection;
        }

        // create and return new query instance
        return new $this->queryClass(array($this, 'executeQuery'), $table, $database);
    }

    /**
     * Translate a query and run the current execution callback
     *
     * @param ClanCats\Hydrahon\BaseQuery               $query
     * @return mixed
     */
    public function executeQuery(BaseQuery $query)
    {
        $translatedArguments = $this->queryTranslator->translate($query);
        $translatedArguments = array_merge(array($query), $translatedArguments);

        return call_user_func_array($this->executionCallback, $translatedArguments);
    }
}
