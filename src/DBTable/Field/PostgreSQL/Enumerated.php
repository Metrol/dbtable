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
     */
    const string PHP_TYPE = 'string';

    /**
     * The Enumerated Type this field uses
     *
     */
    private string $enumType;

    /**
     * The Schema where the Enumerated Type exists
     *
     */
    private string $schema;

    /**
     * List of allowed values for this field that have been assigned
     *
     */
    private array $eVals = [];

    /**
     * Instantiate the object and set up the basics
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue(mixed $inputValue): mixed
    {
        return $inputValue;
    }

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted to their
     * string representations.
     *
     * No quotes or escaping of characters will be performed.
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key = Field\Value::getBindKey();

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $fieldVal;
    }

    /**
     *
     */
    public function getValues(): array
    {
        return $this->eVals;
    }

    /**
     * Set the values that are allowed to be assigned to this field.  Once set,
     * they may not be changed.
     *
     */
    public function setValues(array $values): static
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
     */
    public function getEnumType(): string
    {
        return $this->enumType;
    }

    /**
     * Set the enumerated type this field uses.
     * Once set, this value can not be changed.
     *
     */
    public function setEnumType(string $enumType): static
    {
        if ( ! isset($this->enumType) )
        {
            $this->enumType = $enumType;
        }

        return $this;
    }

    /**
     * Provides the schema that the enumerated type is in
     *
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * Set the schema where the enumerated type exists.
     * Once set, the value can not be changed.
     *
     */
    public function setSchema(string $schema): static
    {
        if ( ! isset($this->schema) )
        {
            $this->schema = $schema;
        }

        return $this;
    }

    /**
     * Looks up the allowed values for the specified enum type and assigns that
     * list to this object.  May only be run once.
     *
     */
    public function runEnumValues(PDO $db): static
    {
        if ( ! empty($this->eVals) )
        {
            return $this;
        }

        if ( ! isset($this->enumType) or ! isset($this->schema) )
        {
            return $this;
        }

        $binding = [
            ':enumtype' => '_'.$this->enumType,
            ':schema'   => $this->schema
        ];

        $sql = <<<SQL
SELECT
    trim(enumlabel) AS enumlabel
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
