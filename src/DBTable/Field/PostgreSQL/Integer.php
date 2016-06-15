<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Integer implements Field
{
    use NameTrait;
    use PropertyTrait;

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
     *
     * @return integer
     */
    public function getMax()
    {
        if ( $this->maxVal == null )
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
        if ( $this->minVal == null )
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
