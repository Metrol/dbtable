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
//    const T_RANGE        = 'range';
//    const T_DTRANGE      = 'daterange';
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
        $primaryKeyLookup = new Reflect\PrimaryKey($this->table, $this->db);
        $primaryKeyLookup->run();
        $primaryKeys = $primaryKeyLookup->output();

        $fieldLookup = new Reflect\Fields($this->table, $this->db);
        $fieldLookup->run();
        $fieldDefList = $fieldLookup->output();

        $this->table->setPrimaryKeyFields($primaryKeys);
        $this->populateFieldsIntoTable($fieldDefList);

        return $this;
    }

    /**
     * Takes the field information that has been passed in and creates Field
     * objects that are then passed into the Table object
     *
     * @param Reflect\FieldDTO[] $fieldDefList
     */
    private function populateFieldsIntoTable(array $fieldDefList): void
    {
        foreach ( $fieldDefList as $fieldDef )
        {
            $field = null;

            switch ( trim($fieldDef->dataType) )
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

                 case self::T_ARRAY:
                     $field = $this->newArrayField($fieldDef);
                     break;

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
    private function newIntegerField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Integer($fieldDef->name);
        $this->setProperties($field, $fieldDef);
        $field->setPrecision($fieldDef->numericPrecision);

        return $field;
    }

    /**
     * Generate a new Numeric field
     *
     */
    private function newNumericField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Numeric($fieldDef->name);
        $this->setProperties($field, $fieldDef);
        $field->setPrecision($fieldDef->numericPrecision);
        $field->setScale($fieldDef->numericScale);

        return $field;
    }

    /**
     * Generate a new Character field
     *
     */
    private function newCharacterField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Character($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        $field->setMaxCharacters( $fieldDef->characterMaximumLength );

        return $field;
    }

    /**
     * Generate a new Boolean field
     *
     */
    private function newBooleanField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Boolean($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new JSON field
     *
     */
    private function newJSONField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\JSON($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new XML field
     *
     */
    private function newXMLField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\XML($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Point field
     *
     */
    private function newPointField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Point($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Date field
     *
     */
    private function newDateField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Date($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Time field
     *
     */
    private function newTimeField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Time($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Array field
     *
     */
    private function newArrayField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\FldArray($fieldDef->name);
        $this->setProperties($field, $fieldDef);

        return $field;
    }

    /**
     * Generate a new Enumerated field
     *
     */
    private function newEnumeratedField(Reflect\FieldDTO $fieldDef): DBTable\Field
    {
        $field = new Fld\Enumerated($fieldDef->name);
        $this->setProperties($field, $fieldDef);
        $field->setEnumType($fieldDef->typeName)
            ->setSchema($fieldDef->typeSchema)
            ->runEnumValues($this->db);

        return $field;
    }

    /**
     * Sets some basic field properties that every field shares
     *
     */
    private function setProperties(DBTable\Field $field, Reflect\FieldDTO $fieldDef): void
    {
        // Set the generic field type pulled from the DB into the Field.
        $field->setDefinedType( $fieldDef->typeName );

        // Is NULL an acceptable value
        if ( $fieldDef->isNullable == 'YES' )
        {
            $field->setNullOk(true);
        }
        else
        {
            $field->setNullOk(false);
        }

        // If there's a default value, let the Field know about it.  The
        // specifics of what to do about that need to be addressed by the Field.
        if ( ! is_null($fieldDef->defaultValue) )
        {
            $field->setDefaultValue($fieldDef->defaultValue);
        }

        if ( ! is_null($fieldDef->comment) )
        {
            $field->setComment($fieldDef->comment);
        }
    }
}
