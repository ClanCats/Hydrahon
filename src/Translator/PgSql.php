<?php namespace ClanCats\Hydrahon\Translator;

/**
 * PgSql query translator
 **
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\BaseQuery;
use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\TranslatorInterface;
use ClanCats\Hydrahon\Exception;

use ClanCats\Hydrahon\Query\Sql\Select;
use ClanCats\Hydrahon\Query\Sql\Insert;
use ClanCats\Hydrahon\Query\Sql\Replace;
use ClanCats\Hydrahon\Query\Sql\Update;
use ClanCats\Hydrahon\Query\Sql\Delete;
use ClanCats\Hydrahon\Query\Sql\Drop;
use ClanCats\Hydrahon\Query\Sql\Truncate;
use ClanCats\Hydrahon\Query\Sql\Func;
use ClanCats\Hydrahon\Query\Sql\Exists;

class PgSql extends Mysql implements TranslatorInterface
{
    
    /**
     * Function to escape identifier names (columns and tables)
     * Doubles backticks, removes null bytes
     * https://dev.mysql.com/doc/refman/8.0/en/identifiers.html
     *
     * @var string
     */
    public function escapeIdentifier($identifier)
    {
        return '\'' . str_replace(array('\'', "\0"), array('\'\'',''), $identifier) . '\'';
    }
}
