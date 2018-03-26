<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting attachment entity.
 *
 * Class Attachment
 *
 * @Assert\GroupSequenceProvider
 */
class Attachment extends Blob implements PersonAwareInterface, OidAwareModelInterface
{
    use OidRequiredAwareModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_inline = false;

    /**
     * {@inheritdoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInline()
    {
        return $this->is_inline;
    }

    /**
     * @param bool $is_inline
     *
     * @return $this
     */
    public function setAsInline($is_inline)
    {
        $this->is_inline = (bool) $is_inline;

        return $this;
    }
}
