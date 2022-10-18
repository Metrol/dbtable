<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable;
use Metrol\DBTable\Field\PostgreSQL as Fld;
use PDO;
use stdClass;
use Exception;

/**
 * Used by the PostgreSQL Table class to lookup all the details about the
 * fields in that table.
 *
 */
class PropertyLookup
{
    /**
     * Field types as reported by PostgreSQL coming from the information schema
     * columns table.
     *
     * @const
     */
    const T_INTEGER      = 'integer';
    const T_BIGINT       = 'bigint';
    const T_SMALLINT     = 'smallint';
    const T_NUMERIC      = 'numeric';
    const T_MONEY        = 'money';
    const T_DOUBLE_PREC  = 'double precision';
    const T_REAL         = 'real';
    const T_VARCHAR      = 'character varying';
    const T_CHAR         = 'character';
    const T_TEXT         = 'text';
    const T_ARRAY        = 'ARRAY';
    const T_RANGE        = 'range';
    const T_DTRANGE      = 'daterange';
    const T_BOOL         = 'boolean';
    const T_ENUM         = 'enum';
    const T_DATE         = 'date';
    const T_TIMESTAMP_TZ = 'timestamp with time zone';
    const T_TIMESTAMP    = 'timestamp without time zone';
    const T_TIME_TZ      = 'time with time zone';
    const T_TIME         = 'time without time zone';
    const T_JSON         = 'json';
    const T_JSONB        = 'jsonb';
    const T_XML          = 'xml';
    const T_POINT        = 'point';

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
     * Instantiate the lookup with a connection and table specified
     *
     */
    public function __construct(DBTable\PostgreSQL $table, PDO $db)
    {
        $this->table = $table;
        $this->db    = $db;
    }

    /**
     * Perform the lookup and populate the table with the fields that are
     * assembled.
     *
     */
    public function run(): static
    {
        $pkFields = $this->findPrimaryKeyFields();
        $this->table->setPrimaryKeyFields($pkFields);

        $fieldDefList = $this->getFieldDefList();

        $this->populateFieldsIntoTable($fieldDefList);

        return $this;
    }

    /**
     * Takes the field information that has been passed in and creates Field
     * objects that are then passed into the Table object
     *
     * @param stdClass[] $fieldDefList
     */
    private function populateFieldsIntoTable(array $fieldDefList): void
    {
        foreach ( $fieldDefList as $fieldDef )
        {
            $field = null;

            switch ( trim($fieldDef->data_type) )
            {
                case self::T_BIGINT:
                case self::T_SMALLINT:
                case self::T_INTEGER:
                    $field = $this->newIntegerField($fieldDef);
                    break;

                case self::T_MONEY:
                case self::T_DOUBLE_PREC:
                case self::T_REAL:
                case self::T_NUMERIC:
                    $field = $this->newNumericField($fieldDef);
                    break;

                case self::T_CHAR:
                case self::T_TEXT:
                case self::T_VARCHAR:
                    $field = $this->newCharacterField($fieldDef);
                    break;

                case self::T_TIMESTAMP:
                case self::T_TIMESTAMP_TZ:
                case self::T_DATE:
                    $field = $this->newDateField($fieldDef);
                    break;

                case self::T_TIME_TZ:
                case self::T_TIME:
                    $field = $this->newTimeField($fieldDef);
                    break;

                // case self::T_ARRAY:
                //     $field = $this->newArrayField($fieldDef);
                //     break;

                case self::T_BOOL:
                    $field = $this->newBooleanField($fieldDef);
                    break;

                case self::T_ENUM:
                    $field = $this->newEnumeratedField($fieldDef);
                    break;

                case self::T_JSON:
                case self::T_JSONB:
                    $field = $this->newJSONField($fieldDef);
                    break;

                case self::T_XML:
                    $field = $this->newXMLField($fieldDef);
                    break;

                case self::T_POINT:
                    $field = $this->newPointField($fieldDef);
                    break;
            }

            if ( ! is_null($field) )
            {
                $this->table->addField($field);
            }
        }
    }

    /**
     * Generate a new Integer field
     *
     */
    private function newIntegerField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Integer($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);
        $field->setPrecision($fieldDef->numeric_precision);

        return $field;
    }

    /**
     * Generate a new Numeric field
     *
     */
    private function newNumericField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Numeric($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);
        $field->setPrecision($fieldDef->numeric_precision);
        $field->setScale($fieldDef->numeric_scale);

        return $field;
    }

    /**
     * Generate a new Character field
     *
     */
    private function newCharacterField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Character($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        $field->setMaxCharacters( $fieldDef->character_maximum_length );

        return $field;
    }

    /**
     * Generate a new Boolean field
     *
     */
    private function newBooleanField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Boolean($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new JSON field
     *
     */
    private function newJSONField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\JSON($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new XML field
     *
     */
    private function newXMLField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\XML($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Point field
     *
     */
    private function newPointField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Point($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Date field
     *
     */
    private function newDateField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Date($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Time field
     *
     */
    private function newTimeField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Time($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Enumerated field
     *
     */
    private function newEnumeratedField(stdClass $fieldDef): DBTable\Field
    {
        $field = new Fld\Enumerated($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);
        $field->setEnumType($fieldDef->udt_name)
            ->setSchema($fieldDef->udt_schema)
            ->runEnumValues($this->db);

        return $field;
    }

    /**
     * Sets some basic field properties that every field shares
     *
     */
    private function setProperties(DBTable\Field $field, stdClass $fieldDef): void
    {
        // Set the generic field type pulled from the DB into the Field.
        $field->setDefinedType( $fieldDef->udt_name );

        // Is NULL an acceptable value
        if ( $fieldDef->is_nullable == 'YES' )
        {
            $field->setNullOk(true);
        }
        else
        {
            $field->setNullOk(false);
        }

        // If there's a default value, let the Field know about it.  The
        // specifics of what to do about that need to be addressed by the Field.
        if ( $fieldDef->column_default !== null )
        {
            $field->setDefaultValue($fieldDef->column_default);
        }
    }

    /**
     * Looks to the database to determine which fields are marked as a primary
     * key.  Returns a list of the field names.
     *
     */
    private function findPrimaryKeyFields(): array
    {
        $sql = <<<SQL
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

        $sth = $this->db->prepare($sql);
        $sth->execute(
            [
                ':table' => $this->table->getName(),
                ':schema' => $this->table->getSchema()
            ]);

        $fetched = $sth->fetchAll(PDO::FETCH_OBJ);

        $rtn = [];

        foreach ( $fetched as $fObj )
        {
            $rtn[] = $fObj->primary_key;
        }

        return $rtn;
    }

    /**
     * Assemble the list of field records for this table
     *
     */
    private function getFieldDefList(): array
    {
        $sql = <<<SQL
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
)

SELECT * FROM fieldlist;
SQL;

        $sth = $this->db->prepare($sql);
        $sth->execute(
            [
                ':table' => $this->table->getName(),
                ':schema' => $this->table->getSchema()
            ]);

        return $sth->fetchAll(PDO::FETCH_OBJ);
    }
}
