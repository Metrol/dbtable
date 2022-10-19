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

class Character implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     */
    const PHP_TYPE = 'string';

    /**
     * Maximum number of characters to be allowed in the string.  If null, there
     * is no maximum.
     *
     */
    private ?int $maxVal;

    /**
     * Instantiate the object and set up the basics
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;

        $this->maxVal = null;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue(mixed $inputValue): mixed
    {
        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and $inputValue === null )
        {
            throw new RangeException('Setting PHP value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when it's okay or
        // convert to an empty string when it isn't.
        if ( $inputValue === null and $this->isNullOk() )
        {
            return null;
        }
        else if ( $inputValue === null and !$this->isNullOk() )
        {
            return '';
        }

        // Without a max value, no need to check for the string length
        if ( $this->maxVal === null or $this->maxVal == 0 )
        {
            return $inputValue;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict and strlen($inputValue) > $this->maxVal )
        {
            throw new RangeException('Setting PHP value of '.$this->fieldName.
                                      ' has too many characters');
        }

        // If not null, not strict and all that, just make sure to truncate to
        // the maximum allowed characters
        return substr($inputValue, 0, $this->maxVal);
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
        $key = Field\Value::getBindKey();
        $val = '';

        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and $inputValue === null )
        {
            throw new RangeException('Setting SQL value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when it's okay or
        // convert to an empty string when it isn't.
        if ( $inputValue === null and $this->isNullOk() )
        {
            return $fieldVal;
        }
        else if ( $inputValue === null and !$this->isNullOk() )
        {
            $fieldVal->setValueMarker($key)
                ->addBinding($key, $val);

            return $fieldVal;
        }

        // Without a max value, no need to check for the string length
        if ( $this->maxVal === null or $this->maxVal == 0 )
        {
            $fieldVal->setValueMarker($key)
                ->addBinding($key, $inputValue);

            return $fieldVal;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict and strlen($inputValue) > $this->maxVal )
        {
            throw new RangeException('Setting SQL value of '.$this->fieldName.
                                      ' has too many characters');
        }

        // If not null, not strict and all that, just make sure to truncate to
        // the maximum allowed characters
        $val = substr($inputValue, 0, $this->maxVal);

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $val);

        return $fieldVal;
    }

    /**
     * How many characters are allowed in the field
     *
     */
    public function getMaxCharacters(): ?int
    {
        return $this->maxVal;
    }

    /**
     * Set how many characters are allowed in the field
     *
     */
    public function setMaxCharacters(int $maxVal = null): static
    {
        $this->maxVal = $maxVal;

        return $this;
    }
}
