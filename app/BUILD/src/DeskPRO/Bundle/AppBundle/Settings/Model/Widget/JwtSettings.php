<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class JwtSettings.
 */
class JwtSettings implements GroupSequenceProviderInterface
{
    /**
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"RequiredSecret"})
     *
     * @var string
     */
    private $secret;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $required = false;

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Common'];
        if ($this->required) {
            $groups[] = 'RequiredSecret';
        }

        return $groups;
    }
}
