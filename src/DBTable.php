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
     */
    public function getName(): string;

    /**
     * Return the database schema, if applicable, for the table
     *
     */
    public function getSchema(): string;

    /**
     * Provide the Fully Qualified Name of the table ready to be applied
     * to an SQL statement without quotes.
     *
     */
    public function getFQN(string $alias = null): string;

    /**
     * Provide the Fully Qualified Name of the table ready to be applied
     * to an SQL statement complete with the appropriate quotes
     *
     */
    public function getFQNQuoted(string $alias = null): string;

    /**
     * Tells the object to look to the database to define the field properties
     * that exist for this table
     *
     */
    public function runFieldLookup(PDO $db): static;

    /**
     * Checks to see if there are fields already loaded up in this object.
     * This doesn't check if all the fields are loaded.  Just if any have been.
     *
     */
    public function isLoaded(): bool;

    /**
     * Adds a field object to the Field set for this table
     *
     */
    public function addField(Field $field): static;

    /**
     * Provide the requested field object for this table
     *
     */
    public function getField(string $fieldName): ?Field;

    /**
     * The list of fields that are in this table
     *
     */
    public function getFields(): Field\Set;

    /**
     * Checks to see if the specified field exists
     *
     */
    public function fieldExists(string $fieldName): bool;

   /**
     * Set which fields are primary keys for the table.
     *
     */
    public function setPrimaryKeyFields(array $primaryKeyFields): static;

    /**
     * Provide the field or fields that make up the primary key
     *
     */
    public function getPrimaryKeys(): array;

    /**
     * Push this table on to the Table Bank to be cached for a future lookup.
     *
     */
    public function bankIt(string $connectionName = null): static;
}
