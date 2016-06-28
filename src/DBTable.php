<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol;

use Metrol\DBTable\Field;
use PDO;

/**
 * A table class needs to be able to fully describe everything about a database
 * table that might need to be known.
 *
 * It must be able to accept from an external source the definition of the
 * fields in that table, but may also poll the database directly for that
 * information.
 *
 */
interface DBTable
{
    /**
     * Provide the basic name of the table
     *
     * @return string
     */
    public function getName();

    /**
     * Return the database schema, if applicable, for the table
     *
     * @return string
     */
    public function getSchema();

    /**
     * Provide the Fully Qualified Name of the table ready to be applied
     * to an SQL statement without quotes.
     *
     * @param string $alias Optional alias suffix for the table
     *
     * @return string
     */
    public function getFQN($alias = null);

    /**
     * Provide the Fully Qualified Name of the table ready to be applied
     * to an SQL statement complete with the appropriate quotes
     *
     * @param string $alias Optional alias suffix for the table
     *
     * @return string
     */
    public function getFQNQuoted($alias = null);

    /**
     * Tells the object to look to the database to define the field properties
     * that exist for this table
     *
     * @param PDO $db Active PDO connection used to lookup properties
     *
     * @return $this
     */
    public function runFieldLookup(PDO $db);

    /**
     * Checks to see if there are fields already loaded up in this object.
     * This doesn't check if all the fields are loaded.  Just if any have been.
     *
     * @return boolean
     */
    public function isLoaded();

    /**
     * Adds a field object to the Field set for this table
     *
     * @param Field $fields
     *
     * @return $this
     */
    public function addField(Field $fields);

    /**
     * Provide the requested field object for this table
     *
     * @param string $fieldName
     *
     * @return Field|null
     */
    public function getField($fieldName);

    /**
     * The list of fields that are in this table
     *
     * @return Field\Set
     */
    public function getFields();

    /**
     * Set which fields are primary keys for the table.
     *
     * @param string[] $primaryKeyFields
     *
     * @return $this
     */
    public function setPrimaryKeyFields(array $primaryKeyFields);

    /**
     * Provide the field or fields that make up the primary key
     *
     * @return string[]
     */
    public function getPrimaryKeys();

    /**
     * Push this table on to the Table Bank to be cached for a future lookup.
     *
     * @param string $connectionName Optional extra info to avoid name conflicts
     *
     * @return $this
     */
    public function bankIt($connectionName = null);
}
