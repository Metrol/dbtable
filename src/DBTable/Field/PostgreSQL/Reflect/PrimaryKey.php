<?php
/**
 * @author        Michael Collette <metrol@metrol.net>
 * @version       2.0
 * @package       Metrol\DBTable
 * @copyright (c) 2024, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL\Reflect;

use Metrol\DBTable;
use PDO;

/**
 * Find the primary key(s) for a given table
 *
 */
class PrimaryKey
{
    /**
     * The database connection to use for the lookup
     *
     */
    private PDO $db;

    /**
     * The table the lookup is being performed on
     *
     */
    private DBTable\PostgreSQL $table;

    /**
     * The primary keys that were found
     *
     */
    private array $primaryKeys = [];

    /**
     * Instantiate the lookup with a connection and table specified
     *
     */
    public function __construct(DBTable\PostgreSQL $table, PDO $db)
    {
        $this->table = $table;
        $this->db    = $db;
    }

    /**
     * Perform the lookup
     *
     */
    public function run(): static
    {
        $sql = $this->getPkQuery();

        $sth = $this->db->prepare($sql);
        $sth->execute(
            [
                ':table' => $this->table->getName(),
                ':schema' => $this->table->getSchema()
            ]);

        $fetched = $sth->fetchAll(PDO::FETCH_OBJ);

        $this->primaryKeys = [];

        foreach ( $fetched as $fObj )
        {
            $this->primaryKeys[] = $fObj->primary_key;
        }

        return $this;
    }

    /**
     * Provide the keys that were found
     *
     */
    public function output(): array
    {
        return $this->primaryKeys;
    }

    /**
     * Provide the SQL needed to look up primary keys
     *
     */
    private function getPkQuery(): string
    {
        return  <<<SQL
WITH schema_ns AS
(
    SELECT
        oid relnamespace
    FROM 
        pg_namespace
    WHERE
        nspname = :schema
),
tbl_class AS
(
    SELECT
        oid tblclassid
    FROM
        pg_class
    WHERE
        relname = :table
        AND
        relnamespace = (
            SELECT
                relnamespace
            FROM
                schema_ns
        )
),
indexs AS
(
    SELECT
        indexrelid
    FROM
        pg_index
    WHERE
        indrelid = (
            SELECT
                tblclassid
            FROM
                tbl_class
            )
        AND
        indisprimary = 't'
),
pk AS
(
    SELECT
        attname primary_key
    FROM
        pg_attribute
   WHERE
        attrelid = (
            SELECT
                indexrelid
            FROM 
                indexs
            )
)

SELECT primary_key FROM pk

SQL;
    }

}
