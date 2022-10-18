<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable;

use RangeException;

/**
 * Every field in a table needs to be able to support this interface.
 *
 */
interface Field
{
    /**
     * Provide the name of the field as is, without any quotes.
     *
     */
    public function getName(): string;

    /**
     * Sets the name of the field.  Once set, this value is immutable.
     *
     */
    public function setName(string $fieldName): static;

    /**
     * Provide the Fully Qualified Name of the field ready to be applied
     * to an SQL binding
     *
     */
    public function getFQN(string $tableAlias = null): string;

    /**
     * Sets the flag to determine if NULL is an acceptable value for this field
     *
     */
    public function setNullOk(bool $flag): static;

    /**
     * Is NULL okay for the field
     *
     */
    public function isNullOk(): bool;

    /**
     * Sets the default value for this field
     *
     */
    public function setDefaultValue(mixed $defaultValue): static;

    /**
     * Provide the default value for this field
     *
     */
    public function getDefaultValue(): mixed;

    /**
     * Provide the defined type name
     *
     */
    public function getDefinedType(): string;

    /**
     * Set the defined type
     *
     */
    public function setDefinedType(string $typeName): static;

    /**
     * Tells the field object not to try and get the value to fit if it's
     * outside the allowed boundaries.  Instead, throw a RangeException for
     * problems found.
     *
     */
    public function setStrictValues(bool $flag = true): static;

    /**
     * The value passed in will be converted to a PHP scalar/array/object
     * depending on the type of field in use.
     *
     * @throws RangeException
     */
    public function getPHPValue(mixed $inputValue): mixed;

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted to their
     * string representations.
     *
     * No quotes or escaping of characters will be performed.
     *
     * @throws RangeException
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value;

    /**
     * Produce the PHP type that can be used in a properties tag for a docBlock
     *
     */
    public function getPHPType(): string;
}
