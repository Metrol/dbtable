<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

/**
 * Provides the place holder and value bindings for a field being put into
 * an SQL statement
 *
 */
class Value
{
    /**
     * The place holder that is used in the SQL string
     *
     * @var string
     */
    public $sqlStr = null;

    /**
     * The bindings to attach to the SQL for storing this value
     *
     * @var array
     */
    public $binding = [];

    /**
     * Instantiate the field value
     *
     */
    public function __construct()
    {

    }

    /**
     * Set the string place holder that will go into the SQL
     *
     * @param string $sql
     *
     * @return $this
     */
    public function setSqlString($sql)
    {
        $this->sqlStr = $sql;

        return $this;
    }

    /**
     * Set the binding array for the field with values
     *
     * @param array $binding
     *
     * @return $this
     */
    public function setBinding($binding)
    {
        $this->binding = $binding;

        return $this;
    }

    /**
     * Set a single binding value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addBinding($key, $value)
    {
        $this->binding[$key] = $value;

        return $this;
    }
}
