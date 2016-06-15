<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

use Metrol\DBTableInterface;
use Metrol\DBTable\FieldInterface;

class PostgreSQL implements FieldInterface
{
    /**
     * Quotes used by PostgreSQL
     *
     * @const
     */
    const FLD_QUOTE      = '"';
    const DATA_QUOTE     = "'";

    /**
     * The table the field belongs to
     *
     * @var DBTableInterface
     */
    protected $table;

    /**
     * The name of the field
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Instantiate the object and setup the basics
     *
     * @param db\Table $table
     * @param string   $fieldName
     */
    public function __construct(db\Table $table, $fieldName)
    {
        $this->table = $table;

        $this->fieldName = $fieldName;
    }

    /**
     * Provide the name of the field as is, without any quotes.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->fieldName;
    }

    /**
     * Provide the Fully Qualified Name of the field ready to be applied
     * to an SQL statement
     *
     * @return string
     */
    public function getFQN(): string
    {
        return self::FLD_QUOTE.$this->fieldName.self::FLD_QUOTE;
    }

    /**
     * Performs a basic check on the value being passed in if it matches the
     * criteria of the field.
     *
     * If able, the method should just truncate the value to what will fit in
     * the field.  Like chopping off the late 20 chars of a 50 char value going
     * into a field that can only hold 30.
     *
     * When not able to cleanly truncate, an Exception should be thrown.  It's
     * up to the implementation to decide what "cleanly" means.
     *
     * When the strict flag is set, no attempt to fix the data will be made.
     * Throw an exception when out of bounds.
     *
     * @param mixed   $value
     * @param boolean $strict
     *
     * @return mixed
     *
     * @throws \LengthException
     */
    public function boundsValue($value, bool $strict = false): mixed
    {
        return $value;
    }

    /**
     *
     * @param mixed   $value
     * @param boolean $strict
     *
     * @return mixed
     *
     * @throws \LengthException
     */
    public function sqlValue($value, bool $strict = false): mixed
    {
        return $value;
    }

}
