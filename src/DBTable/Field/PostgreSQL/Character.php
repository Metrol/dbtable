<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field\PostgreSQL;

use Metrol\DBTable\Field;

class Character implements Field
{
    use NameTrait;
    use PropertyTrait;

    /**
     * Maximum number of characters to be allowed in the string.  If null, there
     * is no maximum.
     *
     * @var integer
     */
    private $maxVal;

    /**
     * Instantiate the object and setup the basics
     *
     * @param string $fieldName
     */
    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;

        $this->maxVal = null;
    }

    /**
     *
     * @return integer
     */
    public function getMaxCharacters()
    {
        return $this->maxVal;
    }

    /**
     *
     * @param integer $maxVal
     *
     * @return $this
     */
    public function setMaxCharacters($maxVal)
    {
        $this->maxVal = $maxVal;

        return $this;
    }
}
