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
     * @const
     */
    const PHP_TYPE = 'string';

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
     * @inheritdoc
     */
    public function getPHPValue($inputValue)
    {
        return $inputValue;
    }

    /**
     * @inheritdoc
     */
    public function getSqlBoundValue($inputValue)
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key      = Field\Value::getBindKey();

        if ( $inputValue === null and !$this->isNullOk() )
        {
            throw new RangeException('Null not allowed for field: '. $this->fieldName);
        }

        $fieldVal->setValueMarker($key)
            ->addBinding($key, $inputValue);

        return $inputValue;
    }
}
