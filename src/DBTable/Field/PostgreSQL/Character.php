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
     * @const
     */
    const PHP_TYPE = 'string';

    /**
     * Maximum number of characters to be allowed in the string.  If null, there
     * is no maximum.
     *
     * @var integer|null
     */
    private $maxVal;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->maxVal = null;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue($inputValue)
    {
        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and $inputValue === null )
        {
            throw new RangeException('Setting PHP value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when its okay or
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
        $rtn = substr($inputValue, 0, $this->maxVal);

        return $rtn;
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

        // When not in strict mode, either keep the null value when its okay or
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
     *
     * @return integer
     */
    public function getMaxCharacters()
    {
        return $this->maxVal;
    }

    /**
     *
     * @param integer $maxVal
     *
     * @return $this
     */
    public function setMaxCharacters($maxVal)
    {
        $this->maxVal = $maxVal;

        return $this;
    }
}
