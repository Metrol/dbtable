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
     */
    const string PHP_TYPE = 'bool';

    /**
     * Instantiate the object and set up the basics
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Insure the default value, when set, stores the actual boolean value
     * against this field.
     *
     */
    public function setDefaultValue(mixed $defaultValue): static
    {
        if ( $defaultValue === true or $defaultValue === 'true' or $defaultValue === 't' )
        {
            $this->defaultValue = true;
        }

        if ( $defaultValue === false or $defaultValue === 'false' or $defaultValue === 'f' )
        {
            $this->defaultValue = false;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue(mixed $inputValue): mixed
    {
        // When the value is already boolean, keep it that way
        if ( $inputValue === true or $inputValue === false )
        {
            return $inputValue;
        }

        if ( is_null($inputValue) )
        {
            if ( $this->isNullOk() )
            {
                return null;
            }
            else if ( $this->strict )
            {
                throw new RangeException('Setting PHP value of '.$this->fieldName.
                                         ' to null is not allowed');
            }
            else
            {
                if ( ! is_null($this->getDefaultValue()) )
                {
                    return $this->getDefaultValue();
                }
                else
                {
                    return false;
                }
            }
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
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $field = new Field\Value($this->fieldName);
        $key   = Field\Value::getBindKey();

        $phpVal = $this->getPHPValue($inputValue);

        // Set the default value
        if ( $this->isNullOk() )
        {
            $value = null;
        }
        else if ( ! is_null($this->defaultValue) )
        {
            $value = $this->defaultValue;
        }
        else
        {
            $value = false;
        }

        if ( is_null($phpVal) )
        {
            $value = null;
        }
        elseif ( $phpVal === true )
        {
            $value = 'true';
        }
        elseif ( $phpVal === false )
        {
            $value = 'false';
        }

        $field->setValueMarker($key)
              ->addBinding($key, $value);

        return $field;
    }
}
