<?php
/**
 * @author        "Michael Collette" <metrol@metrol.net>
 * @package       Metrol\DBTable
 * @version       2.0
 * @copyright (c) 2016, Michael Collette
 */

namespace Metrol\DBTable\Field;

trait StrictModeTrait
{
    /**
     * Flag for string value handling
     *
     */
    private bool $strict = false;

    /**
     * Tells the field object not to try and get the value to fit if it's
     * outside the allowed boundaries.  Instead, throw a RangeException for
     * problems found.
     *
     */
    public function setStrictValues(bool $flag = true): static
    {
        if ( $flag )
        {
            $this->strict = true;
        }
        else
        {
            $this->strict = false;
        }

        return $this;
    }
}
