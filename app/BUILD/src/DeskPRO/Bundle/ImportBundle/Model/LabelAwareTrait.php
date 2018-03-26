<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LabelAwareTrait.
 */
trait LabelAwareTrait
{
    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $labels = [];

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel($label)
    {
        $this->labels[] = (string) $label;

        return $this;
    }
}
