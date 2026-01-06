<?php
/**
 * @author        Michael Collette <metrol@metrol.net>
 * @version       2.0
 * @package       Metrol\DBTable
 * @copyright (c) 2024, Michael Collette
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
        return new Field\Value($this->fieldName);
    }
}
