<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

/**
 * Provides the placeholder and value bindings for a field being put into
 * an SQL statement
 *
 */
class Value
{
    /**
     * The name of the field
     *
     */
    private string $fieldName;

    /**
     * The placeholder that is used in the SQL string
     *
     */
    private string $valueMarker;

    /**
     * The bindings to attach to the SQL for storing this value
     *
     */
    private array $binding = [];

    /**
     * Instantiate the field value
     *
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Provide the name of the field
     *
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Set the string placeholder that will go into the SQL
     *
     */
    public function setValueMarker(string $sql): static
    {
        $this->valueMarker = $sql;

        return $this;
    }

    /**
     * Provide the string to put into the SQL statement
     *
     */
    public function getValueMarker(): string|null
    {
        if ( ! isset($this->valueMarker) )
        {
            return null;
        }

        return $this->valueMarker;
    }

    /**
     * Set a single binding value
     *
     */
    public function addBinding(string $key, mixed $value): static
    {
        $this->binding[$key] = $value;

        return $this;
    }

    /**
     * Provide the bound values array
     *
     */
    public function getBoundValues(): array
    {
        return $this->binding;
    }

    /**
     * Provide the number of items bound to this field value
     *
     */
    public function getBindCount(): int
    {
        return count($this->binding);
    }

    /**
     * Provide a unique binding key
     *
     */
    static public function getBindKey(): string
    {
        return uniqid(':_') . '_';
    }
}
