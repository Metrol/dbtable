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

class Point implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'array';

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
     * Converts the database value to an array when used in a PHP context
     *
     * @param string $inputValue
     *
     * @return array|null
     */
    public function getPHPValue($inputValue)
    {
        if ( $inputValue === null )
        {
            return null;
        }

        $rtn = [];
        $inpStr = $inputValue;

        // Strip the parantheses from the string
        $inpStr = str_replace('(', '', $inpStr);
        $inpStr = str_replace(')', '', $inpStr);

        // Get the values between the commas
        list ( $x, $y ) = explode(',', $inpStr);
        $rtn[] = floatval($x);
        $rtn[] = floatval($y);

        return $rtn;
    }

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Arrays will be converted to their string
     * representations.
     *
     * No quotes or escaping of characters will be performed.
     *
     * @param array $inputValue
     *
     * @return Field\Value
     *
     * @throws RangeException
     */
    public function getSqlBoundValue($inputValue)
    {
        $rtn = new Field\Value($this->fieldName);

        if ( is_array($inputValue) )
        {
            if ( count($inputValue) < 2 )
            {
                throw new RangeException('Must have an array with 2 values for a Point field');
            }

            list($x, $y) = $inputValue;

            $x = floatval($x);
            $y = floatval($y);

            $xKey = uniqid(':');
            $yKey = uniqid(':');

            $rtn->setValueMarker( 'point('. $xKey .', '. $yKey .')' )
                ->addBinding($xKey, $x)
                ->addBinding($yKey, $y);
        }
        else
        {
            throw new RangeException('Point fields must be assigned an array of 2 values');
        }

        return $rtn;
    }
}
