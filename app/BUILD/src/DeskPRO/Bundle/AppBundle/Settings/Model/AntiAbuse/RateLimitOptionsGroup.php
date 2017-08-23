<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
