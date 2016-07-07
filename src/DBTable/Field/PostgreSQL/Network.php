<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable;

class Network implements DBTable\Field, DBTable\FieldValue
{
    use NameTrait;
    use PropertyTrait;

    /**
     * Flag for string value handling
     *
     * @var boolean
     */
    private $strict;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
        $this->strict    = false;
    }

    /**
     * @inheritdoc
     */
    public function setStrictValues($flag = true)
    {
        if ( $flag )
        {
            $this->strict = true;
        }
        else
        {
            $this->strict = false;
        }

        return $this;
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
