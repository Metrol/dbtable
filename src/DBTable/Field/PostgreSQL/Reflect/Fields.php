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
 * Look into the database to fetch the fields for a table
 *
 */
class Fields
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
     * The list of fields found from the lookup
     *
     * @var FieldDTO[]
     */
    private array $fieldSet;

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
        $sql = $this->getFieldQuery();

        $sth = $this->db->prepare($sql);
        $sth->execute(
            [
                ':table' => $this->table->getName(),
                ':schema' => $this->table->getSchema()
            ]);

        $fetched = $sth->fetchAll(PDO::FETCH_OBJ);

        $this->fieldSet = [];

        foreach ( $fetched as $fObj )
        {
            $dto = new FieldDTO;
            $dto->name                   = $fObj->column_name;
            $dto->dataType               = $fObj->data_type;
            $dto->isNullable             = $fObj->is_nullable;
            $dto->defaultValue           = $fObj->column_default;
            $dto->characterMaximumLength = $fObj->character_maximum_length;
            $dto->numericPrecision       = $fObj->numeric_precision;
            $dto->numericScale           = $fObj->numeric_scale;
            $dto->typeSchema             = $fObj->udt_schema;
            $dto->typeName               = $fObj->udt_name;
            $dto->comment                = $fObj->column_comment;

            $this->fieldSet[] = $dto;
        }

        return $this;
    }

    /**
     * Provide the list of field data transfer objects that were fetched from
     * the database
     *
     * @return FieldDTO[]
     */
    public function output(): array
    {
        return $this->fieldSet;
    }

    /**
     * Assemble the list of field records for this table
     *
     */
    public function getFieldQuery(): string
    {
        return <<<SQL
WITH type_list AS
(
    SELECT
        typname,
        typnamespace,
        typtype
    FROM
        pg_type
),
fieldlist_prelim AS
(
    SELECT
        column_name,
        is_nullable,
        column_default,
        data_type,
        character_maximum_length,
        numeric_precision,
        numeric_scale,
        udt_schema,
        udt_name
    FROM
        information_schema.columns
    WHERE
        table_schema = :schema
        AND
        table_name = :table
),
fieldlist AS
(
    SELECT
        fp.column_name,
        fp.is_nullable,
        fp.column_default,
        CASE WHEN data_type = 'USER-DEFINED' THEN
            (
                SELECT
                    CASE
                       WHEN tp.typtype = 'b' THEN
                           'base'
                       WHEN tp.typtype = 'c' THEN
                           'composite'
                       WHEN tp.typtype = 'd' THEN
                           'domain'
                       WHEN tp.typtype = 'e' THEN
                           'enum'
                       WHEN tp.typtype = 'p' THEN
                           'psuedo'
                       WHEN tp.typtype = 'r' THEN
                           'range'
                    END
                FROM
                    type_list tp
                WHERE
                    tp.typname = fp.udt_name
                    AND tp.typnamespace = (
                        SELECT
                            oid
                        FROM
                            pg_namespace ns
                        WHERE
                            ns.nspname = fp.udt_schema
                    )
            )
        ELSE
            data_type
        END AS data_type,
        character_maximum_length,
        numeric_precision,
        numeric_scale,
        udt_schema,
        udt_name
    FROM
        fieldlist_prelim fp
),
comments AS
(
    SELECT
        cols.column_name,
        (
            SELECT
                pg_catalog.col_description(c.oid, cols.ordinal_position::int)
            FROM
                pg_catalog.pg_class c
            WHERE
                c.oid = (
                    SELECT ('"' || :schema || '"."' || :table || '"')::regclass::oid
                )
              AND
                c.relname = cols.table_name
        ) AS column_comment
    FROM
        information_schema.columns cols
    WHERE
        cols.table_name   = :table
        AND
        cols.table_schema = :schema
),
summary AS
(
    SELECT
        fl.column_name,
        fl.is_nullable,
        fl.column_default,
        fl.data_type,
        fl.character_maximum_length,
        fl.numeric_precision,
        fl.numeric_scale,
        fl.udt_schema,
        fl.udt_name,
        c.column_comment
    FROM
        fieldlist fl
        LEFT JOIN comments c
            on fl.column_name = c.column_name
)
SELECT * FROM summary;
SQL;
    }
}
