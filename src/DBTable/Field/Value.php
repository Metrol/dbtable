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
     * The name of the field
     *
     * @var string
     */
    private $fieldName = null;

    /**
     * The place holder that is used in the SQL string
     *
     * @var string
     */
    private $valueMarker = null;

    /**
     * The bindings to attach to the SQL for storing this value
     *
     * @var array
     */
    private $binding = [];

    /**
     * Instantiate the field value
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Provide the name of the field
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Set the string place holder that will go into the SQL
     *
     * @param string $sql
     *
     * @return $this
     */
    public function setValueMarker(string $sql): self
    {
        $this->valueMarker = $sql;

        return $this;
    }

    /**
     * Provide the string to put into the SQL statement
     *
     * @return string|null
     */
    public function getValueMarker()
    {
        return $this->valueMarker;
    }

    /**
     * Set a single binding value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addBinding(string $key, $value): self
    {
        $this->binding[$key] = $value;

        return $this;
    }

    /**
     * Provide the bound values array
     *
     * @return array
     */
    public function getBoundValues(): array
    {
        return $this->binding;
    }

    /**
     * Provide the number of items bound to this field value
     *
     * @return integer
     */
    public function getBindCount(): int
    {
        return count($this->binding);
    }

    /**
     * Provide a unique binding key
     *
     * @return string
     */
    static public function getBindKey(): string
    {
        return uniqid(':_') . '_';
    }
}
