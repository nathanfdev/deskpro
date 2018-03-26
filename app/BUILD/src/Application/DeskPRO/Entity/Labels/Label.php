<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity\Labels;

/**
 * Interface Label.
 */
interface Label
{
    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * Return label type (e.g. person, feedback etc).
     *
     * @return string
     */
    public function getType();
}
