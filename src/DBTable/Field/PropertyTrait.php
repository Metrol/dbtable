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
     * @var boolean
     */
    private $nullOk = true;

    /**
     * Default value for this field, if such a default was set.
     *
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * Is this field a primary key?
     *
     * @var boolean
     */
    private $primaryKey = false;

    /**
     * The UDT name of the field as reported by the query.
     *
     * @var string
     */
    private $udtName = '';

    /**
     * @inheritdoc
     */
    public function setNullOk($flag)
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
    public function isNullOk()
    {
        return $this->nullOk;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     *
     * @return string
     */
    public function getDefinedType()
    {
        return $this->udtName;
    }

    /**
     *
     * @param string $typeName
     *
     * @return $this
     */
    public function setDefinedType($typeName)
    {
        $this->udtName = $typeName;

        return $this;
    }
}
