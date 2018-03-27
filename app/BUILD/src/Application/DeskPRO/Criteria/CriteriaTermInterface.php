<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Criteria;

use Orb\Util\CheckedOptionsArray;

interface CriteriaTermInterface
{
    /**
     * Gets the type name of the criteria.
     *
     * @return string
     */
    public function getTermType();

    /**
     * Gets criteria operator (is, is not, etc).
     *
     * @return string
     */
    public function getTermOperator();

    /**
     * Get's an array of options.
     *
     * @return CheckedOptionsArray
     */
    public function getTermOptions();
}
