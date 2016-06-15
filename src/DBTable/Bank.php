<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable;

use Metrol\DBTable;

/**
 * Stores tables within this object so they do not need to be looked up more
 * than a single time.
 *
 */
class Bank
{
    /**
     * The instance of this object.
     *
     * @var Bank
     */
    private static $instance;

    /**
     * Cached copies of the already instantiated
     *
     * @var DBTable[]
     */
    private $tables = [];

    /**
     * Singleton object, only one instance allowed
     *
     */
    private function __construct()
    {
        // nothing to do here
    }

    /**
     * Generate and instance of this object for internal callers only.
     *
     * @return Bank
     */
    protected static function getInstance()
    {
        if ( !is_object(self::$instance) )
        {
            self::$instance = new Bank;
        }

        return self::$instance;
    }

    /**
     * Make a deposit in the bank
     *
     * @param DBTable $table
     * @param string  $connectionName Only needed for multiple connections
     */
    public static function deposit(DBTable $table, $connectionName = null)
    {
        $key = $table->getName();

        // If the table has no name, get out of here now.
        //
        if ( empty($key) )
        {
            return;
        }

        if ( ! empty($table->getSchema()) )
        {
            $key = $table->getSchema(). '.' .$key;
        }

        if ( ! empty($connectionName) )
        {
            $key = $connectionName.':'.$key;
        }

        self::getInstance()->tables[$key] = $table;
    }

    /**
     * Attempt to withdraw a table from the bank.  Returns null if that table
     * isn't found.
     *
     * @param string $tableName
     * @param string $schema
     * @param string $connectionName
     *
     * @return DBTable|null
     */
    public static function get($tableName, $schema = null, $connectionName = null)
    {
        $inst = self::getInstance();

        if ( empty($tableName) )
        {
            return null;
        }

        $key = $tableName;

        if ( ! empty($schema) )
        {
            $key = $schema. '.' .$key;
        }

        if ( ! empty($connectionName) )
        {
            $key = $connectionName.':'.$key;
        }

        if ( isset($inst->tables[$key]) )
        {
            return $inst->tables[$key];
        }

        return null;
    }
}
