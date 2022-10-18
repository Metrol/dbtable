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
     */
    const PHP_TYPE = 'array';

    /**
     * Value to return for valid JSON string
     *
     */
    const JSON_OK = 'OK';

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
     *
     * @throws RangeException
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key      = Field\Value::getBindKey();

        // Handle an okay null value
        if ( $inputValue === null and $this->isNullOk() )
        {
            $key = Field\Value::getBindKey();

            $fieldVal->setValueMarker($key)
                     ->addBinding($key, null);

            return $fieldVal;
        }

        // Silently deal with a null that's not allowed when not in strict mode
        if ( $inputValue === null and !$this->isNullOk() and !$this->strict )
        {
            $key = Field\Value::getBindKey();

            $fieldVal->setValueMarker($key)
                     ->addBinding($key, '');

            return $fieldVal;
        }

        // Null value that's not okay, and in strict mode.  Throw exception!
        if ( $inputValue === null and !$this->isNullOk() and $this->strict )
        {
            throw new RangeException('Null not allowed for field: '. $this->fieldName);
        }

        $validJSON = $this->validateJSON($inputValue);

        // Not valid and in strict mode, throw an exception with error message
        if ( $validJSON !== self::JSON_OK and $this->strict )
        {
            throw new RangeException('JSON Field Error: '.$validJSON);
        }

        // Not valid, not strict, and nulls are okay.  Null is the value!
        if ( $validJSON !== self::JSON_OK and $this->isNullOk() )
        {
            $fieldVal->setValueMarker($key)
                ->addBinding($key, null);

            return $fieldVal;
        }

        // Not valid, not strict, and nulls not okay.  Empty string for a value
        if ( $validJSON !== self::JSON_OK and !$this->isNullOk() )
        {
            $fieldVal->setValueMarker($key)
                ->addBinding($key, '');

            return $fieldVal;
        }

        // Everything appears okay, so process the field value
        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $fieldVal;
    }

    /**
     * Verify a string is actually JSON.
     * If successful, returns 'OK'.  Otherwise, an error message.
     *
     */
    protected function validateJSON(string $jsonToTest): string
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
