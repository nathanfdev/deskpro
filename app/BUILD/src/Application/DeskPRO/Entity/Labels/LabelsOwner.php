<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity\Labels;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface LabelsOwner.
 */
interface LabelsOwner
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param Label $label
     */
    public function addLabel(Label $label);

    /**
     * @param Label $label
     */
    public function removeLabel(Label $label);

    /**
     * @return Label[]|ArrayCollection
     */
    public function getLabels();

    public function clearLabels();
}
