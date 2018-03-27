<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Exporting custom field entity.
 *
 * Class CustomField
 *
 * @Assert\GroupSequenceProvider
 */
class CustomField implements GroupSequenceProviderInterface, OidAwareModelInterface
{
    use OidAwareModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"name"})
     */
    private $name;

    /**
     * @var mixed
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"common"})
     */
    private $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Custom field value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set a value.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['common'];
        if (!$this->oid) {
            $groups[] = 'name';
        }

        return $groups;
    }
}
