<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

trait NameTrait
{
    /**
     * The name of this field
     *
     */
    private string $fieldName = '';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->fieldName;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $fieldName): static
    {
        if ( empty($this->fieldName) )
        {
            $this->fieldName = $fieldName;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFQN(string|null $tableAlias = null): string
    {
        $rtn = '';

        if ( ! empty($tableAlias) )
        {
            $rtn .= $tableAlias . '.';
        }

        $rtn .= $this->fieldName;

        return $rtn;
    }
}
