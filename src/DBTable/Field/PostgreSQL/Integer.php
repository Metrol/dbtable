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

class Integer implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'integer';

    /**
     * Multiplier to raise the bytes by to get the range of the integer
     *
     * @const
     */
    const BYTE_MULT = 256;

    /**
     * Number of bits in a byte.
     *
     * @const
     */
    const BITS_PER_BYTE = 8;

    /**
     * If not specified, assume this will be a 4 byte integer
     *
     * @const
     */
    const DEFAULT_PRECISION = 4;

    /**
     * The number of bytes used to represent this number.
     *
     * @var integer
     */
    private $precision;

    /**
     * Calculated maximum value this integer may hold based on the precision
     *
     * @var integer
     */
    private $maxVal;

    /**
     * Calculated minimum value this integer may hold based on the precision
     *
     * @var integer
     */
    private $minVal;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
        $this->precision = self::DEFAULT_PRECISION;
        $this->maxVal    = null;
        $this->minVal    = null;
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
        // convert to a 0 when it isn't
        if ( $inputValue === null and $this->isNullOk() )
        {
            return null;
        }
        else if ( $inputValue === null and !$this->isNullOk() )
        {
            return 0;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict )
        {
            if ( $inputValue != intval($inputValue) )
            {
                throw new RangeException('Setting PHP value of ' .
                                          $this->fieldName .
                                          ' is not an integer');
            }

            if ( $inputValue < $this->getMin() or $inputValue > $this->getMax() )
            {
                throw new RangeException('Setting PHP value of ' .
                                          $this->fieldName .
                                          ' is outside of what is allowed');
            }
        }

        // No strict mode issues, no null issues... go ahead and make sure this
        // value is an integer
        $rtn = intval($inputValue);

        // If things are out of range at this point, reset the value to 0 or
        // null depending on which is allowed
        if ( $rtn < $this->getMin() or $rtn > $this->getMax() )
        {
            if ( $this->isNullOk() )
            {
                $rtn = null;
            }
            else
            {
                $rtn = 0;
            }
        }

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
        $key      = Field\Value::getBindKey();

        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and $inputValue === null )
        {
            throw new RangeException('Setting SQL value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when its okay or
        // convert to a 0 when it isn't
        if ( $inputValue === null and $this->isNullOk() )
        {
            $fieldVal->setValueMarker($key)
                ->addBinding($key, null);

            return $fieldVal;
        }
        else if ( $inputValue === null and !$this->isNullOk() )
        {
            $fieldVal->setValueMarker($key)
                     ->addBinding($key, 0);

            return $fieldVal;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict )
        {
            if ( $inputValue != intval($inputValue) )
            {
                throw new RangeException('Setting SQL value of ' .
                                          $this->fieldName .
                                          ' is not an integer');
            }

            if ( $inputValue < $this->getMin() or $inputValue > $this->getMax() )
            {
                throw new RangeException('Setting SQL value of ' .
                                          $this->fieldName .
                                          ' is outside of what is allowed');
            }
        }

        // No strict mode issues, no null issues... go ahead and make sure this
        // value is an integer
        $value = intval($inputValue);

        // If things are out of range at this point, reset the value to 0 or
        // null depending on which is allowed
        if ( $value < $this->getMin() or $value > $this->getMax() )
        {
            if ( $this->isNullOk() )
            {
                $value = null;
            }
            else
            {
                $value = 0;
            }
        }

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $value);

        return $fieldVal;
    }

    /**
     *
     * @return integer
     */
    public function getMax()
    {
        if ( $this->maxVal === null )
        {
            $this->maxVal = pow(self::BYTE_MULT, $this->precision) / 2;
            $this->maxVal--;
        }

        return $this->maxVal;
    }

    /**
     *
     * @return integer
     */
    public function getMin()
    {
        if ( $this->minVal === null )
        {
            $this->minVal = pow(self::BYTE_MULT, $this->precision) / 2 * -1;
        }

        return $this->minVal;
    }

    /**
     * Set the precision of this integer in bits.  This value must be divisible
     * by 8 so that it represents the number of bytes involved.
     *
     * @param mixed $precision
     *
     * @return $this;
     */
    public function setPrecision($precision)
    {
        $prec = intval($precision);

        if ( $prec % self::BITS_PER_BYTE == 0 )
        {
            $this->precision = intval($precision) / self::BITS_PER_BYTE;
        }

        return $this;
    }
}
