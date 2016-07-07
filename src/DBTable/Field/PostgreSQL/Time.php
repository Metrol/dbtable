<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable;

class Time implements DBTable\Field, DBTable\FieldValue
{
    use NameTrait;
    use PropertyTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = '\DateTime';

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

    /**
     * @inheritdoc
     */
    public function getPhpType()
    {
        return self::PHP_TYPE;
    }
}
