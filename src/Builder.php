<?php 

namespace ClanCats\Hydrahon;

/**
 * Query Builder manager
 **
 * @link      https://github.com/ClanCats/Hydrahon/
 * @copyright Copyright (c) 2015-2019 Mario Döring
 * @license   https://github.com/ClanCats/Hydrahon/blob/master/LICENSE (MIT License)
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
     * @throws \ClanCats\Hydrahon\Exception
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

        static::$grammar[$grammarKey] = [$queryBuilder, $queryTranslator];
    }

    /**
     * The current query builder instance
     *
     * @var BaseQuery
     */
    protected $queryBuilder = null;

    /**
     * Currently loaded query translator
     *
     * @var \ClanCats\Hydrahon\TranslatorInterface
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
     * @throws \ClanCats\Hydrahon\Exception
     *
     * @param string                $grammarKey
     * @param callable              $executionCallback
     * @return void
     */
    public function __construct(string $grammarKey, callable $executionCallback)
    {
        if (!isset(static::$grammar[$grammarKey])) 
        {
            throw new Exception('There is no Hydrahon grammar "' . $grammarKey . '" registered.');
        }

        $this->executionCallback = $executionCallback;

        // prepare the current grammar
        [$queryBuilderClass, $translatorClass] = static::$grammar[$grammarKey];

        // create the query builder specific instances
        $this->queryTranslator = new $translatorClass;
        $this->queryBuilder = new $queryBuilderClass;

        // assign the result fetcher
        $this->queryBuilder->setResultFetcher([$this, 'executeQuery']);

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
     * @param string                        $method
     * @param array                         $arguments
     * @return BaseQuery
     */
    public function __call(string $method, array $arguments)
    {
        return [$this->queryBuilder,$method](...$arguments);
    }

    /**
     * Translate the given query
     * 
     * @param BaseQuery                 $query
     * @return array
     */
    public function translateQuery(BaseQuery $query): array
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
        return ($this->executionCallback)($query,...$this->translateQuery($query));
    }
}
