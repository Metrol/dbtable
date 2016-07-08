<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable;

/**
 * Every field in a table needs to be able to support this interface.
 *
 */
interface Field
{
    /**
     * Provide the name of the field as is, without any quotes.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name of the field.  Once set, this value is immutable.
     *
     * @param string $fieldName
     *
     * @return $this
     */
    public function setName($fieldName);

    /**
     * Provide the Fully Qualified Name of the field ready to be applied
     * to an SQL binding
     *
     * @param  string $tableAlias Puts the table alias on the field name
     *
     * @return string
     */
    public function getFQN($tableAlias = null);

    /**
     * Sets the flag to determine if NULL is an acceptable value for this field
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setNullOk($flag);

    /**
     * Is NULL okay for the field
     *
     * @return boolean
     */
    public function isNullOk();

    /**
     * Sets the default value for this field
     *
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue);

    /**
     * Provide the default value for this field
     *
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * Provide the defined type name
     *
     * @return string
     */
    public function getDefinedType();

    /**
     *
     * @param string $typeName
     *
     * @return $this
     */
    public function setDefinedType($typeName);

    /**
     * Tells the field object not to try and get the value to fit if it's
     * outside the allowed boundaries.  Instead, throw a RangeException for
     * problems found.
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setStrictValues($flag = true);

    /**
     * The value passed in will be converted to a PHP scalar/array/object
     * depending on the type of field in use.
     *
     * @param mixed $inputValue
     *
     * @return mixed
     *
     * @throws \RangeException
     */
    public function getPHPValue($inputValue);

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted to their
     * string representations.
     *
     * No quotes or escaping of characters will be performed.
     *
     * @param mixed $inputValue
     *
     * @return mixed
     *
     * @throws \RangeException
     */
    public function getSqlBoundValue($inputValue);
}
