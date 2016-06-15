<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use PDO;
use Metrol\DBTable;
use Metrol\DBTable\Field\PostgreSQL as Fld;

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

    /**
     * The database connection to use for the lookup
     *
     * @var PDO
     */
    private $db;

    /**
     * The table the lookup is being performed on
     *
     * @var DBTable\PostgreSQL
     */
    private $table;

    /**
     * Instantiate the object and setup the basics
     *
     * @param DBTable\PostgreSQL $table
     * @param PDO                $db
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
     * @return $this
     */
    public function run()
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
     * @param \stdClass[] $fieldDefList
     */
    private function populateFieldsIntoTable(array $fieldDefList)
    {
        foreach ( $fieldDefList as $fieldDef )
        {
            $field = null;

            switch ( trim($fieldDef->data_type) )
            {
                case self::T_INTEGER:
                    $field = $this->newIntegerField($fieldDef);
                    break;

                case self::T_BIGINT:
                    $field = $this->newIntegerField($fieldDef);
                    break;

                case self::T_SMALLINT:
                    $field = $this->newIntegerField($fieldDef);
                    break;

                case self::T_NUMERIC:
                    $field = $this->newNumericField($fieldDef);
                    break;

                case self::T_MONEY:
                    $field = $this->newNumericField($fieldDef);
                    break;

                case self::T_DOUBLE_PREC:
                    $field = $this->newNumericField($fieldDef);
                    break;

                case self::T_VARCHAR:
                    $field = $this->newCharacterField($fieldDef);
                    break;

                case self::T_CHAR:
                    $field = $this->newCharacterField($fieldDef);
                    break;

                case self::T_TEXT:
                    $field = $this->newCharacterField($fieldDef);
                    break;

                case self::T_ARRAY:
                    $field = $this->newArrayField($fieldDef);
                    break;

                case self::T_BOOL:
                    $field = $this->newBooleanField($fieldDef);
                    break;

                case self::T_ENUM:
                    $field = $this->newEnumeratedField($fieldDef);
                    break;
            }

            if ( $field != null )
            {
                $this->table->addField($field);
            }
        }
    }

    /**
     * Generate a new Integer field
     *
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newIntegerField(\stdClass $fieldDef)
    {
        $field = new Fld\Integer($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);
        $field->setPrecision($fieldDef->numeric_precision);

        return $field;
    }

    /**
     * Generate a new Numeric field
     *
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newNumericField(\stdClass $fieldDef)
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
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newCharacterField(\stdClass $fieldDef)
    {
        $field = new Fld\Character($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        $field->setMaxCharacters( $fieldDef->character_maximum_length );

        return $field;
    }

    /**
     * Generate a new Array field
     *
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newArrayField(\stdClass $fieldDef)
    {
        $field = new Fld\Arrays($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Boolean field
     *
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newBooleanField(\stdClass $fieldDef)
    {
        $field = new Fld\Boolean($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Enumerated field
     *
     * @param \stdClass $fieldDef
     *
     * @return DBTable\Field
     */
    private function newEnumeratedField(\stdClass $fieldDef)
    {
        $field = new Fld\Enumerated($fieldDef->column_name);
        $this->setProperties($field, $fieldDef);

        $eVals = $this->getEnumValues($fieldDef->udt_schema, $fieldDef->udt_name);
        $field->setValues($eVals);

        return $field;
    }

    /**
     * Sets some of the basic field properties that every field shares
     *
     * @param DBTable\Field $field
     * @param \stdClass     $fieldDef
     */
    private function setProperties(DBTable\Field $field, \stdClass $fieldDef)
    {
        // Set the generic field type pulled from the DB into the Field.
        $field->setUdtName( $fieldDef->udt_name );

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

        // If the Field has been noted as a primary key in the Table object,
        // then set the flag in the Field itself.
        if ( in_array($field->getName(), $this->table->getPrimaryKeys()) )
        {
            $field->setPrimaryKey(true);
        }
        else
        {
            $field->setPrimaryKey(false);
        }
    }

    /**
     * Looks up the allowed values for the specified enum type
     *
     * @param string $schema
     * @param string $enumType
     *
     * @return string[] List of allowed values
     */
    private function getEnumValues($schema, $enumType)
    {
        $binding = [
            ':enumtype' => '_'.$enumType,
            ':schema'   => $schema
        ];

        $sql = <<<SQL
SELECT
    trim(enumlabel) enumlabel
FROM
    pg_catalog.pg_enum e
    JOIN pg_catalog.pg_type t
        ON e.enumtypid = t.typelem
        AND t.typname = :enumtype
    JOIN pg_catalog.pg_namespace n
        ON n.oid = t.typnamespace
        AND n.nspname = :schema
ORDER BY
    e.enumsortorder ASC

SQL;

        $sth = $this->db->prepare($sql);
        $sth->execute($binding);

        $rtn = [];

        while ( $row = $sth->fetch(PDO::FETCH_NUM) )
        {
            $rtn[] = $row[0];
        }

        return $rtn;
    }

    /**
     * Looks to the database to determine which fields are marked as a primary
     * key.  Returns a list of the field names.
     *
     * @return string[]
     */
    private function findPrimaryKeyFields()
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
     * @return array
     */
    private function getFieldDefList()
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
        END data_type,
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
