<?php
/**
 * @author        Michael Collette <mcollette@meetingevolution.net>
 * @version       1.0
 * @package       Sourcing
 * @copyright (c) 2024, Meeting Evolution
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

/**
 * Describe purpose of FldArray
 *
 */
class FldArray implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

    /**
     * Specifies the type of array this is
     *
     */
    private Field $arrayType;

    /**
     * Instantiate FldArray
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function getPHPValue(mixed $inputValue): array
    {


        return $inputValue;
    }

    public function getSqlBoundValue(mixed $inputValue): Field\Value
    {
        $fieldVal = new Field\Value($this->fieldName);
        $key = Field\Value::getBindKey();
        $val = '';

        return $fieldVal;
    }
}
