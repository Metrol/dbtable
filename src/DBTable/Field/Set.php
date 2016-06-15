<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol_Libs
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

use \Metrol\DBTable\Field;

/**
 * Maintains a list of Database Table Fields that a Table object will use to
 * determine what kind of structure it has.
 *
 */
class Set implements \Iterator, \Countable
{
    /**
     * The fields being stored, keyed by the name of the field
     *
     * @var Field[]
     */
    protected $fields;

    /**
     * Instantiate the object and store the sample DB Item as a reference
     *
     */
    public function __construct()
    {
        $this->fields = array();
    }

    /**
     * Fetch a field from the set by name.  Returns null if not found
     *
     * @param string $fieldName
     *
     * @return Field|null
     */
    public function getField($fieldName)
    {
        $rtn = null;

        if ( isset($this->fields[$fieldName]) )
        {
            $rtn = $this->fields[$fieldName];
        }

        return $rtn;
    }

    /**
     * Adds a field to the set
     *
     * @param Field $field
     *
     * @return $this
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * How many fields are in this set.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * Implementing the Iterartor interface to walk through the fields in this
     * set.
     *
     */
    public function rewind()
    {
        reset($this->fields);
    }

    /**
     *
     * @return Field
     */
    public function current()
    {
        return current($this->fields);
    }

    /**
     *
     * @return string
     */
    public function key()
    {
        return key($this->fields);
    }

    /**
     *
     * @return Field
     */
    public function next()
    {
        return next($this->fields);
    }

    /**
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->current() !== false;
    }

}
