<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable;

/**
 * Specifies the methods Fields will need to support cleaning and validating
 * data coming from the Database to PHP and back again.
 *
 */
interface FieldValue
{
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
