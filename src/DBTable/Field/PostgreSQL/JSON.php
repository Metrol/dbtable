<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;
use RangeException;

class JSON implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const string
     */
    const PHP_TYPE = 'array';

    /**
     * Value to return for valid JSON string
     *
     * @const string
     */
    const JSON_OK = 'OK';

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
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
     *
     * @throws RangeException
     */
    public function getSqlBoundValue($inputValue)
    {
        $fieldVal = new Field\Value;
        $key      = uniqid(':');

        $validJSON = $this->validateJSON($inputValue);

        // Not valid and in strict mode, throw an exception with error message
        if ( $validJSON !== self::JSON_OK and $this->strict )
        {
            throw new RangeException('JSON Field Error: '.$validJSON);
        }

        // Not valid, not strict, and nulls are okay.  Null is the value!
        if ( $validJSON !== self::JSON_OK and $this->isNullOk() )
        {
            $fieldVal->setSqlString($key)
                ->addBinding($key, null);

            return $fieldVal;
        }

        // Not valid, not strict, and nulls not okay.  Empty string for a value
        if ( $validJSON !== self::JSON_OK and !$this->isNullOk() )
        {
            $fieldVal->setSqlString($key)
                ->addBinding($key, '');

            return $fieldVal;
        }

        // Everything appears okay, so process the field value
        $fieldVal->setSqlString($key)
            ->addBinding($key, $inputValue);

        return $fieldVal;
    }

    /**
     * Verify a string is actually JSON
     *
     * @param string $jsonToTest
     *
     * @return string If successfull, returns 'OK'.  Otherwise, and error message
     */
    protected function validateJSON($jsonToTest)
    {
        // Attempt to decode the JSON data
        json_decode($jsonToTest);

        // Switch and check possible JSON errors
        switch (json_last_error())
        {
            case JSON_ERROR_NONE:
                $jsonStatus = self::JSON_OK; // JSON is valid // No error has occurred
                break;

            case JSON_ERROR_DEPTH:
                $jsonStatus = 'The maximum stack depth has been exceeded.';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                $jsonStatus = 'Invalid or malformed JSON.';
                break;

            case JSON_ERROR_CTRL_CHAR:
                $jsonStatus = 'Control character error, possibly incorrectly encoded.';
                break;

            case JSON_ERROR_SYNTAX:
                $jsonStatus = 'Syntax error, malformed JSON.';
                break;

            case JSON_ERROR_UTF8:
                $jsonStatus = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;

            case JSON_ERROR_RECURSION:
                $jsonStatus = 'One or more recursive references in the value to be encoded.';
                break;

            case JSON_ERROR_INF_OR_NAN:
                $jsonStatus = 'One or more NAN or INF values in the value to be encoded.';
                break;

            case JSON_ERROR_UNSUPPORTED_TYPE:
                $jsonStatus = 'A value of a type that cannot be encoded was given.';
                break;

            default:
                $jsonStatus = 'Unknown JSON error occured.';
                break;
        }

        return $jsonStatus;
    }
}
