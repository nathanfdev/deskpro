<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Entity label interface.
 *
 * Interface LabelAwareInterface
 */
interface LabelAwareModelInterface
{
    /**
     * Returns a collection of labels.
     *
     * @return string[]
     */
    public function getLabels();

    /**
     * Set labels.
     *
     * @param string[] $labels
     *
     * @return $this
     */
    public function setLabels(array $labels);

    /**
     * Add a new label.
     *
     * @param string $label
     *
     * @return $this
     */
    public function addLabel($label);
}
