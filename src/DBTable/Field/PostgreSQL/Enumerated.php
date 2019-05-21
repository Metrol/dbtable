<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;
use PDO;

class Enumerated implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'string';

    /**
     * The Enumerated Type this field uses
     *
     * @var string
     */
    private $enumType;

    /**
     * The Schema where the Enumerated Type exists
     *
     * @var string
     */
    private $schema;

    /**
     * List of allowed values for this field that have been assigned
     *
     * @var array
     */
    private $eVals;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->enumType = null;
        $this->schema   = null;
        $this->eVals    = [];
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue($inputValue)
    {
        return $inputValue;
    }

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted to their
     * string representations.
     *
     * No quotes or escaping of characters will be performed.
     *
     * @param mixed $inputValue
     *
     * @return Field\Value
     */
    public function getSqlBoundValue($inputValue)
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key = Field\Value::getBindKey();

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $fieldVal;
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->eVals;
    }

    /**
     * Set the values that are allowed to be assigned to this field.  Once set,
     * they may not be changed.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        if ( empty($this->eVals) )
        {
            $this->eVals = $values;
        }

        return $this;
    }

    /**
     * Provides the enumerated type this field uses.
     *
     * @return string
     */
    public function getEnumType()
    {
        return $this->enumType;
    }

    /**
     * Set the enumerated type this field uses.
     * Once set, this value can not be changed.
     *
     * @param string $enumType
     *
     * @return $this
     */
    public function setEnumType($enumType)
    {
        if ( $this->enumType == null )
        {
            $this->enumType = $enumType;
        }

        return $this;
    }

    /**
     * Provides the schema that the enumerated type is in
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Set the schema where the enumerated type exists.
     * Once siet, the value can not be changed.
     *
     * @param string $schema
     *
     * @return $this
     */
    public function setSchema($schema)
    {
        if ( $this->schema == null )
        {
            $this->schema = $schema;
        }

        return $this;
    }

    /**
     * Looks up the allowed values for the specified enum type and assigns that
     * list to this object.  May only be run once.
     *
     * @param PDO $db
     *
     * @return $this
     */
    public function runEnumValues(PDO $db)
    {
        if ( !empty($this->eVals) )
        {
            return $this;
        }

        if ( empty($this->enumType) or empty($this->schema) )
        {
            return $this;
        }

        $binding = [
            ':enumtype' => '_'.$this->enumType,
            ':schema'   => $this->schema
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
    e.enumsortorder

SQL;

        $sth = $db->prepare($sql);
        $sth->execute($binding);

        while ( $row = $sth->fetch(PDO::FETCH_NUM) )
        {
            $this->eVals[] = $row[0];
        }

        return $this;
    }
}
