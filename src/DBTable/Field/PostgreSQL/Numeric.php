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

/**
 * Provides a field definition for any number type that includes decimals
 *
 */
class Numeric implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     */
    const string PHP_TYPE = 'float';

    /**
     * How many digits of precision used
     *
     */
    protected int $precision;

    /**
     * The scale of decimal places used
     *
     */
    protected int $scale;

    /**
     * Instantiate the object and set up the basics
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue(mixed $inputValue): float|null
    {
        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and is_null($inputValue) )
        {
            throw new RangeException('Setting PHP value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when it's okay or
        // convert to a 0 when it isn't
        if ( is_null($inputValue) and $this->isNullOk() )
        {
            return null;
        }
        else if ( is_null($inputValue) and !$this->isNullOk() )
        {
            return 0;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict )
        {
            if ( $inputValue < $this->getMin() or $inputValue > $this->getMax() )
            {
                throw new RangeException('Setting PHP value of ' .
                                          $this->fieldName .
                                          ' is outside of what is allowed');
            }
        }

        // No strict mode issues, no null issues... go ahead and make sure this
        // value is rounded to what is allowed
        $rtn = round(floatval($inputValue), $this->scale);

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
     * @inheritdoc
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and is_null($inputValue) )
        {
            throw new RangeException('Setting SQL value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        $fieldValue = new Field\Value($this->fieldName);
        $key        = Field\Value::getBindKey();

        // When not in strict mode, either keep the null value when it's okay or
        // convert to a 0 when it isn't
        if ( is_null($inputValue) and $this->isNullOk() )
        {
            $fieldValue->setValueMarker($key)
                ->addBinding($key, null);

            return $fieldValue;
        }
        else if ( is_null($inputValue) and !$this->isNullOk() )
        {
            $fieldValue->setValueMarker($key)
                ->addBinding($key, 0);

            return $fieldValue;
        }

        // Throw an exception when in strict mode and the value is too large
        if ( $this->strict )
        {
            if ( $inputValue < $this->getMin() or $inputValue > $this->getMax() )
            {
                throw new RangeException('Setting SQL value of ' .
                                          $this->fieldName .
                                          ' is outside of what is allowed');
            }
        }

        // No strict mode issues, no null issues... go ahead and make sure this
        // value is rounded to what is allowed
        $value = round($inputValue, $this->scale);

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

        $fieldValue->setValueMarker($key)
            ->addBinding($key, $value);

        return $fieldValue;
    }


    /**
     * Set the precision of this type
     *
     */
    public function setPrecision(int|null $digits = null): static
    {
        if ( is_null($digits) )
        {
            $this->precision = 0;

            return $this;
        }

        $this->precision = $digits;

        return $this;
    }

    /**
     * Set the scale, defining the number of digits to the right of the decimal
     *
     */
    public function setScale(int|null $digits = null): static
    {
        if ( is_null($digits) )
        {
            $this->scale = 0;

            return $this;
        }

        $this->scale = $digits;

        return $this;
    }

    /**
     *
     */
    public function getMax(): float|null
    {
        $p = $this->precision;
        $s = $this->scale;

        if ( ! isset($p) or ! isset($s) )
        {
            return null;
        }

        return pow(10, $p - $s) - pow(10, $s * -1);
    }

    /**
     *
     */
    public function getMin(): float|null
    {
        $p = $this->precision;
        $s = $this->scale;

        if ( ! isset($p) or ! isset($s) )
        {
            return null;
        }

        $max = pow(10, $p - $s) - pow(10, $s * -1);

        return $max * -1;
    }
}
