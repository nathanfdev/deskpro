<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RateLimitLockoutGroup.
 */
class RateLimitLockoutGroup extends AbstractRateLimitGroup
{
    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     *
     * @JMS\Type("integer")
     */
    protected $lockoutTime = 0;

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
}
