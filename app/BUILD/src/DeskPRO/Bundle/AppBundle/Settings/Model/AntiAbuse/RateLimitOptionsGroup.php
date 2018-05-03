<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class RateLimitOptionsGroup.
 *
 * @Assert\GroupSequenceProvider
 */
class RateLimitOptionsGroup extends AbstractRateLimitGroup implements GroupSequenceProviderInterface
{
    const RESPONSE_LOCKOUT = 'lockout';
    const RESPONSE_CAPTCHA = 'captcha';

    /**
     * How to respond when limit was hit.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $response = self::RESPONSE_LOCKOUT;

    /**
     * @var int
     *
     * @Assert\NotBlank(groups={"Lockout"})
     * @Assert\GreaterThan(value=0, groups={"Lockout"})
     *
     * @JMS\Type("integer")
     */
    protected $lockoutTime = 0;

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return int
     */
    public function getLockoutTime()
    {
        return $this->lockoutTime;
    }

    /**
     * @param int $lockoutTime
     *
     * @return $this
     */
    public function setLockoutTime($lockoutTime)
    {
        $this->lockoutTime = $lockoutTime;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Default'];
        if ($this->response === self::RESPONSE_LOCKOUT) {
            $groups[] = 'Lockout';
        }

        return $groups;
    }
}
