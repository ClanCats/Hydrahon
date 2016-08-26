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
        list($queryBuilderClass, $translatorClass) = static::$grammar[$grammarKey];

        // create the query builder specific instances
        $this->queryTranslator = new $translatorClass;
        $this->queryBuilder = new $queryBuilderClass;

        // assign the result fetcher
        $this->queryBuilder->setResultFetcher(array($this, 'executeQuery'));

        // check if the translator is valid
        if (!$this->queryTranslator instanceof TranslatorInterface)
        {
            throw new Exception('A query translator must implement the "TranslatorInterface" interface.');
        }

        // check if the query builder is an instance of Base Query
        if (!$this->queryBuilder instanceof BaseQuery)
        {
            throw new Exception('A query builder must be an instance of the "BaseQuery".');
        }
    }

    /**
     * Forwards calls to the current query builder
     * 
     * @param string                        $table
     * @return ClanCats\Hydrahon\BaseQuery
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->queryBuilder, $method), $arguments);
    }

    /**
     * Translate the given query
     * 
     * @param BaseQuery                 $query
     * @return array
     */
    public function translateQuery(BaseQuery $query)
    {
        return $this->queryTranslator->translate($query);
    }

    /**
     * Translate a query and run the current execution callback
     *
     * @param BaseQuery               $query
     * @return mixed
     */
    public function executeQuery(BaseQuery $query)
    {
        return call_user_func_array($this->executionCallback, array_merge(array($query), $this->translateQuery($query)));
    }
}
