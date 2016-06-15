<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

/**
 * Provides a field definition for any number type that includes decimals
 *
 */
class Numeric implements Field
{
    use NameTrait;
    use PropertyTrait;

    /**
     * How many digits of precision used
     *
     * @var integer
     */
    protected $precision;

    /**
     * The scale of decimal places used
     *
     * @var integer
     */
    protected $scale;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->precision = null;
        $this->scale     = null;
    }

    /**
     * Set the precision of this type
     *
     * @param integer
     *
     * @return $this
     */
    public function setPrecision($digits)
    {
        $this->precision = intval($digits);

        return $this;
    }

    /**
     * Set the scale, defining the number of digits to the right of the decimal
     *
     * @param integer
     *
     * @return $this
     */
    public function setScale($digits)
    {
        $this->scale = intval($digits);

        return $this;
    }

    /**
     *
     * @return float|null
     */
    public function getMax()
    {
        $p = $this->precision;
        $s = $this->scale;

        if ( $p == null or $s == null )
        {
            return null;
        }

        $max = pow(10, $p - $s) - pow(10, $s * -1);

        return $max;
    }

    /**
     *
     * @return float
     */
    public function getMin()
    {
        $p = $this->precision;
        $s = $this->scale;

        if ( $p == null or $s == null )
        {
            return null;
        }

        $max = pow(10, $p - $s) - pow(10, $s * -1);
        $min = $max * -1;

        return $min;
    }

}
