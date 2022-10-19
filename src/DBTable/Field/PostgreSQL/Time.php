<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Time implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     */
    const PHP_TYPE = '\DateTime';

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
     * The value passed in will be converted to a format ready to be bound
     * to a SQL engine execute.  Objects and arrays will be converted a Field
     * Value object.
     *
     */
    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key      = Field\Value::getBindKey();

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $fieldVal;
    }
}
