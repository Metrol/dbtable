<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

trait PropertyTrait
{
    /**
     * Is this field allowed to be set to null?
     *
     */
    private bool $nullOk = true;

    /**
     * Default value for this field, if such a default was set.
     *
     */
    private mixed $defaultValue = null;

    /**
     * Is this field a primary key?
     *
     */
    private bool $primaryKey = false;

    /**
     * The UDT name of the field as reported by the query.
     *
     */
    private string $udtName = '';

    /**
     * @inheritdoc
     */
    public function setNullOk(bool $flag): static
    {
        if ( $flag )
        {
            $this->nullOk = true;
        }
        else
        {
            $this->nullOk = false;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isNullOk(): bool
    {
        return $this->nullOk;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultValue(mixed $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     *
     */
    public function getDefinedType(): string
    {
        return $this->udtName;
    }

    /**
     *
     */
    public function setDefinedType(string $typeName): static
    {
        $this->udtName = $typeName;

        return $this;
    }

    /**
     * Provide the PHP Type for this object based on the class constant
     *
     */
    public function getPhpType(): string
    {
        $rtn = '';

        if ( defined('self::PHP_TYPE') )
        {
            $rtn = self::PHP_TYPE;
        }

        return $rtn;
    }
}
