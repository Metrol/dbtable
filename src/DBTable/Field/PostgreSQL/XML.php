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

class XML implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     */
    const PHP_TYPE = 'string';

    /**
     * Instantiate the object and set up the basics
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function getPHPValue(mixed $inputValue): mixed
    {
        return $inputValue;
    }

    /**
     * @inheritdoc
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key      = Field\Value::getBindKey();

        // Handle an okay null value
        if ( is_null($inputValue) and $this->isNullOk() )
        {
            $key = Field\Value::getBindKey();

            $fieldVal->setValueMarker($key)
                ->addBinding($key, null);

            return $fieldVal;
        }

        // Silently deal with a null that's not allowed when not in strict mode
        if ( is_null($inputValue) and !$this->isNullOk() and !$this->strict )
        {
            $key = Field\Value::getBindKey();

            $fieldVal->setValueMarker($key)
                ->addBinding($key, '');

            return $fieldVal;
        }

        // Null value that's not okay, and in strict mode.  Throw exception!
        if ( is_null($inputValue) and !$this->isNullOk() and $this->strict )
        {
            throw new RangeException('Null not allowed for field: '. $this->fieldName);
        }

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $inputValue;
    }
}
