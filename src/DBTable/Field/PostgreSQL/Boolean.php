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

class Boolean implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'bool';

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
        $rtn = false; // Default value if nothing else can manage to set

        // When the value is already boolean, keep it that way
        if ( $inputValue === true or $inputValue === false )
        {
            return $inputValue;
        }

        // PostgreSQL will always have a t or f as the value for true/false
        // Also check for the value of 'true' or 'false' as a string
        if ( strtolower($inputValue) === 't' or strtolower($inputValue) === 'true' )
        {
            return true;
        }

        if ( strtolower($inputValue) === 'f' or strtolower($inputValue) === 'false' )
        {
            return false;
        }

        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and $inputValue == null )
        {
            throw new \RangeException('Setting PHP value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when its okay or
        // convert to a false if it isn't
        if ( $inputValue == null and $this->isNullOk() )
        {
            return null;
        }
        else if ( $inputValue == null and !$this->isNullOk() )
        {
            return $rtn;
        }

        // If we actually made it this far, default to PHP handling of boolean
        // logic to figure this out.
        if ( $inputValue )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getSqlBoundValue($inputValue)
    {
        $field = new Field\Value($this->fieldName);
        $key   = Field\Value::getBindKey();

        if ( $inputValue === null and $this->strict and !$this->isNullOk() )
        {
            throw new RangeException('Setting SQL value of '.$this->fieldName.
                                     ' to null is not allowed');
        }

        if ( $inputValue === null and !$this->strict and !$this->isNullOk() )
        {
            $field->setValueMarker($key)
                ->addBinding($key, 'false');

            return $field;
        }

        $phpVal = $this->getPHPValue($inputValue);

        switch ( $phpVal )
        {
            case null:
                $value = null;
                break;

            case true:
                $value = 'true';
                break;

            case false:
                $value = 'false';
                break;

            default:
                $value = null;
        }

        $field->setValueMarker($key)
              ->addBinding($key, $value);

        return $field;
    }
}
