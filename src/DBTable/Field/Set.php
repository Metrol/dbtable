<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol_Libs
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

use Countable;
use Iterator;
use Metrol\DBTable\Field;

/**
 * Maintains a list of Database Table Fields that a Table object will use to
 * determine what kind of structure it has.
 *
 */
class Set implements Iterator, Countable
{
    /**
     * The fields being stored, keyed by the name of the field
     *
     * @var Field[]
     */
    protected array $fields = [];

    /**
     * Instantiate the object and store the sample DB Item as a reference
     *
     */
    public function __construct()
    {

    }

    /**
     * Fetch a field from the set by name.  Returns null if not found
     *
     */
    public function getField(string $fieldName): ?Field
    {
        $rtn = null;

        if ( $this->fieldExists($fieldName) )
        {
            $rtn = $this->fields[$fieldName];
        }

        return $rtn;
    }

    /**
     * Adds a field to the set
     *
     */
    public function addField(Field $field): static
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * Checks to see if the specified field exists
     *
     */
    public function fieldExists(string $fieldName): bool
    {
        $rtn = false;

        if ( isset($this->fields[$fieldName]) )
        {
            $rtn = true;
        }

        return $rtn;
    }

    /**
     * Provide a dump of properties that can be used for a docBlock in an
     * object dynamically dealing with fields.
     *
     */
    public function getDocBlockProperties(): string
    {
        $out = '';

        $typeLen = 0;
        $nameLen = 0;

        foreach ( $this as $field )
        {
            $type = $field->getPHPType();
            $name = '$' . $field->getName();

            if ( strlen($type) > $typeLen )
            {
                $typeLen = strlen($type);
            }

            if ( strlen($name) > $nameLen )
            {
                $nameLen = strlen($name);
            }
        }

        foreach ( $this as $field )
        {
            $type    = $field->getPHPType();
            $name    = '$' . $field->getName();
            $comment = $field->getComment();

            $out .= ' * @property ';
            $out .= str_pad($type, $typeLen + 1);


            if ( ! empty($comment) )
            {
                $out .= str_pad($name, $nameLen + 1);
                $out .= $field->getComment();
            }
            else
            {
                $out .= $name;
            }

            $out .= PHP_EOL;
        }

        return $out;
    }

    /**
     * How many fields are in this set.
     *
     */
    public function count(): int
    {
        return count($this->fields);
    }

    /**
     * Implementing the Iterator interface to walk through the fields in this
     * set.
     *
     */
    public function rewind(): void
    {
        reset($this->fields);
    }

    public function current(): Field|false
    {
        return current($this->fields);
    }

    public function key(): string
    {
        return key($this->fields);
    }

    public function next(): Field|false
    {
        return next($this->fields);
    }

    public function valid(): bool
    {
        return $this->current() != false;
    }
}
