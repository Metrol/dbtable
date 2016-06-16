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
     * to an SQL statement
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
     * Sets the flag to mark this field as a primary key
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setPrimaryKey($flag);

    /**
     * Is this field a primary key?
     *
     * @return mixed
     */
    public function isPrimaryKey();

    /**
     *
     * @return string
     */
    public function getUdtName();

    /**
     *
     * @param string $udtName
     *
     * @return $this
     */
    public function setUdtName($udtName);

    /**
     * Provide the PHP type that is exepected out of this field.
     *
     * @return string
     */
    public function getPhpType();
}
