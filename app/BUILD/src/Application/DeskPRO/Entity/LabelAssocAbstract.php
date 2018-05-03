<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Entity\Labels\Label;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base labels associations class.
 */
abstract class LabelAssocAbstract extends \Application\DeskPRO\Domain\DomainObject implements Label
{
    /**
     * The 'type' of label this is for, as it could be found in the
     * LabelDef.
     */
    const LABEL_TYPENAME = 'OVERRIDE';

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $label;

    public function __construct($value = null)
    {
        if ($value) {
            $this->setLabel($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $label = trim($label);
        $label = str_replace(',', '', $label);
        $this->setModelField('label', $label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return static::LABEL_TYPENAME;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->label;
    }
}
