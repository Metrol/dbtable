<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class BitString implements Field
{
    use Field\NameTrait, Field\PropertyTrait, Field\StrictModeTrait;

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
        return $inputValue;
    }
}
