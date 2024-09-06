<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable;

use PDO;
use Metrol\DBTable;
use Metrol\DBTable\Field\PostgreSQL\PropertyLookup;

class PostgreSQL implements DBTable
{
    /**
     * The name of the table
     *
     */
    private string $name;

    /**
     * The database schema the table resides in
     *
     */
    private string $schema;

    /**
     * The list of Field objects that make up this table.
     *
     */
    private Field\Set $fields;

    /**
     * The fields in this table, if any, that make up the primary key
     *
     */
    private array $primaryKeyFields;

    /**
     * Define that table name and schema.  Instantiate the rest of the
     * properties.
     *
     */
    public function __construct(string $name, string|null $schema = null)
    {
        $this->name             = $name;
        $this->schema           = empty($schema) ? 'public' : $schema;
        $this->fields           = new Field\Set;
        $this->primaryKeyFields = [];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getFQN(string|null $alias = null): string
    {
        $rtn = '';

        if ( !empty($this->schema) )
        {
            $rtn .= $this->schema;
            $rtn .= '.';
        }

        $rtn .= $this->name;

        if ( !empty($alias) )
        {
            $rtn .= ' '.$alias;
        }

        return $rtn;
    }

    /**
     * @inheritdoc
     */
    public function getFQNQuoted(string|null $alias = null): string
    {
        $rtn = '';

        if ( !empty($this->schema) )
        {
            $rtn .= '"'.$this->schema.'".';
        }

        $rtn .= '"'.$this->name.'"';

        if ( !empty($alias) )
        {
            $rtn .= ' "'.$alias.'"';
        }

        return $rtn;
    }

    /**
     * @inheritdoc
     */
    public function runFieldLookup(PDO $db): static
    {
        (new PropertyLookup($this, $db))->run();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isLoaded(): bool
    {
        $rtn = false;

        if ( $this->fields->count() > 0 )
        {
            $rtn = true;
        }

        return $rtn;
    }

    /**
     * @inheritdoc
     */
    public function addField(Field $field): static
    {
        $this->fields->addField($field);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getField(string $fieldName): Field|null
    {
        return $this->fields->getField($fieldName);
    }

    /**
     * @inheritdoc
     */
    public function fieldExists(string $fieldName): bool
    {
        return $this->fields->fieldExists($fieldName);
    }

    /**
     * @inheritdoc
     */
    public function getFields(): Field\Set
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function setPrimaryKeyFields(array $primaryKeyFields): static
    {
        $this->primaryKeyFields = $primaryKeyFields;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeys(): array
    {
        return $this->primaryKeyFields;
    }

    /**
     * @inheritdoc
     */
    public function bankIt(string|null $connectionName = null): static
    {
        DBTable\Bank::deposit($this, $connectionName);

        return $this;
    }
}
