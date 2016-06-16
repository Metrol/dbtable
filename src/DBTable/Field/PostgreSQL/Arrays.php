<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Arrays implements Field
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
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->field = null;
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
