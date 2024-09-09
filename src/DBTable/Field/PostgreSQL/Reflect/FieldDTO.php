<?php
/**
 * @author        Michael Collette <metrol@metrol.net>
 * @version       2.0
 * @package       Metrol\DBTable
 * @copyright (c) 2024, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL\Reflect;

/**
 * Describe purpose of FieldDTO
 *
 */
class FieldDTO
{
    /**
     * The field name
     *
     */
    public string $name;

    /**
     * The type of field
     *
     */
    public string $dataType;

    /**
     * Is the field nullable.  YES/NO
     *
     */
    public string $isNullable;

    /**
     * Default value of the field
     *
     */
    public string|null $defaultValue;

    /**
     * For text fields, how many characters are allowed.  Null otherwise.
     *
     */
    public int|null $characterMaximumLength;

    /**
     * Number of digits of preciscion.  Used for numeric and integer types
     *
     */
    public int|null $numericPrecision;

    /**
     * How many decimal places to account for
     *
     */
    public int|null $numericScale;

    /**
     * The schema where the data type is defined
     *
     */
    public string $typeSchema;

    /**
     * The SQL name of the data type
     *
     */
    public string $typeName;

    /**
     * Comments attached to the field
     *
     */
    public string|null $comment;

    /**
     * Instantiate FieldDTO
     *
     */
    public function __construct()
    {
    }
}
