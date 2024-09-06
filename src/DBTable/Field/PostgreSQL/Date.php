<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Exception;
use Metrol\DBTable\Field;
use DateTime;
use DateTimeZone;
use RangeException;

class Date implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * Date formats needed for the different fields in play
     *
     */
    const FMT_DATE        = 'Y-m-d';
    const FMT_DATETIME    = 'Y-m-d H:i:s';
    const FMT_DATETIME_TZ = 'Y-m-d H:i:s T';

    const DEF_TIMEZONE    = 'UTC';

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'DateTime';

    /**
     * Instantiate the object and set up the basics
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Will provide back a DateTime object based on the string value passed in.
     *
     * If the value is already a DateTime object, then it will just pass the
     * same object back.
     *
     * @throws Exception
     * @throws RangeException
     */
    public function getPHPValue(mixed $inputValue): DateTime|null
    {
        // In strict mode, if null is not okay and the value is null then we
        // need to throw an error.
        if ( $this->strict and !$this->isNullOk() and is_null($inputValue) )
        {
            throw new RangeException('Setting PHP value of '.$this->fieldName.
                                      ' to null is not allowed');
        }

        // When not in strict mode, either keep the null value when it's okay or
        // convert to an empty string when it isn't.
        if ( is_null($inputValue) and $this->isNullOk() )
        {
            return null;
        }
        else if ( is_null($inputValue) and !$this->isNullOk() )
        {
            return new DateTime;
        }

        if ( is_object($inputValue) )
        {
            if ( $inputValue instanceof DateTime )
            {
                return $inputValue;
            }
            else
            {
                return new DateTime;
            }
        }

        if ( $this->getDefinedType() == 'timestamp' )
        {
            // When the time zone isn't specified by the DB, force it to UTC
            $timeZone = new DateTimeZone(self::DEF_TIMEZONE);
            $dateObj  = new DateTime($inputValue, $timeZone);
        }
        else
        {
            $dateObj = new DateTime($inputValue);
        }

        return $dateObj;
    }

    /**
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted to their
     * string representations.
     *
     * No quotes or escaping of characters will be performed.
     *
     * @throws Exception
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key      = Field\Value::getBindKey();

        $dateObj = $this->getPHPValue($inputValue);

        if ( is_null($dateObj) )
        {
            if ( $this->isNullOk() )
            {
                $fieldVal->setValueMarker($key)
                         ->addBinding($key, null);

                return $fieldVal;
            }
            else
            {
                $dateObj = new DateTime;
            }
        }

        $fmt = self::FMT_DATE;

        switch ( $this->getDefinedType() )
        {
            case 'timestamp':
            case PropertyLookup::T_TIMESTAMP:
                $fmt = self::FMT_DATETIME;
                break;

            case PropertyLookup::T_TIMESTAMP_TZ:
                $fmt = self::FMT_DATETIME_TZ;
                break;
        }

        $fieldVal->setValueMarker($key)
                 ->addBinding($key, $dateObj->format($fmt));

        return $fieldVal;
    }
}
