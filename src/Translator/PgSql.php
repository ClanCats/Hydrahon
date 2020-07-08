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

    /**
     * get and escape the table name
     *
     * @return string
     */
    protected function escapeTable($allowAlias = true)
    {
        $table = $this->attr('table');
        $database = $this->attr('database');
        $buffer = '';

        if (!is_null($database))
        {
            $buffer .= $database . '.';
        }

        // when the table is an array we have a table with alias
        if (is_array($table)) 
        {
            reset($table);

            // the table might be a subselect so check that
            // first and compile the select if it is one
            if ($table[key($table)] instanceof Select)
            {
                $translator = new static;

                // translate the subselect
                list($subQuery, $subQueryParameters) = $translator->translate($table[key($table)]);

                // merge the parameters
                foreach($subQueryParameters as $parameter)
                {
                    $this->addParameter($parameter);
                }

                return '(' . $subQuery . ') as ' . $this->escape(key($table));
            }

            // otherwise continue with normal table
            if ($allowAlias)
            {
                $table = key($table) . ' as ' . $table[key($table)];
            } else {
                $table = key($table);
            }
        }

        return $buffer . $table;    
    }
}
