<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable;

class Arrays implements DBTable\Field, DBTable\FieldValue
{
    use NameTrait;
    use PropertyTrait;

    /**
     * What kind of PHP type should be expected from a field like this.
     *
     * @const
     */
    const PHP_TYPE = 'array';

    /**
     * The kind of Field that is allowed in this array
     *
     * @var Field
     */
    private $field;

    /**
     * Flog for string value handling
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
        $this->field     = null;
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
     * Sets the type that is allowed into the array
     *
     * @param Field $field
     *
     * @return $this
     */
    public function setArrayFieldType(Field $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPhpType()
    {
        return self::PHP_TYPE;
    }
}
