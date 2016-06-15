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
     * @var string
     */
    private $name;

    /**
     * The database schema the table resides in
     *
     * @var string
     */
    private $schema;

    /**
     * The list of Field objects that make up this table.
     *
     * @var Field\Set
     */
    private $fields;

    /**
     * The fields in this table, if any, that make up the primary key
     *
     * @var string[]
     */
    private $primaryKeyFields;

    /**
     * Define that table name and schema.  Instantiate the rest of the
     * properties.
     *
     * @param string $name
     * @param string $schema
     */
    public function __construct($name, $schema = null)
    {
        $this->name             = $name;
        $this->schema           = empty($schema) ? 'public' : $schema;
        $this->fields           = new Field\Set;
        $this->primaryKeyFields = array();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getFQN($alias = null)
    {
        $rtn = '"';

        if ( !empty($this->schema) )
        {
            $rtn .= $this->schema;
            $rtn .= '".';
        }

        $rtn .= $this->name;
        $rtn .= '"';

        if ( !empty($alias) )
        {
            $rtn .= ' '.$alias;
        }

        return $rtn;
    }

    /**
     * @inheritdoc
     */
    public function runFieldLookup(PDO $db)
    {
        (new PropertyLookup($this, $db))->run();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addField(Field $field)
    {
        $this->fields->addField($field);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getField($fieldName)
    {
        return $this->fields->getField($fieldName);
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function setPrimaryKeyFields(array $primaryKeyFields)
    {
        $this->primaryKeyFields = $primaryKeyFields;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeyFields;
    }

    /**
     * @inheritdoc
     */
    public function bankIt($connectionName = null)
    {
        DBTable\Bank::deposit($this, $connectionName);
    }
}
