<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Setting.
 */
class Setting implements PrimaryImportModelInterface
{
    use PrimaryImportModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotNull()
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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
