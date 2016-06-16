<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Range implements Field
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
    public function getPhpType()
    {
        return self::PHP_TYPE;
    }
}
