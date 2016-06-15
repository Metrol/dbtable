<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Enumerated implements Field
{
    use NameTrait;
    use PropertyTrait;

    /**
     * List of allowed values for this field that have been assigned
     *
     * @var array
     */
    private $eVals;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->eVals = [];
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->eVals;
    }

    /**
     * Set the values that are allowed to be assigned to this field.  Once set,
     * they may not be changed.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        if ( empty($this->eVals) )
        {
            $this->eVals = $values;
        }

        return $this;
    }
}
