<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse;

use DeskPRO\Bundle\AppBundle\Settings\Model\EnabledOptionTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractRateLimitGroup.
 */
abstract class AbstractRateLimitGroup
{
    use EnabledOptionTrait;

    /**
     * The limit itself.
     *
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     *
     * @JMS\Type("integer")
     */
    protected $limit = 0;

    /**
     * Time period for limit.
     *
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     *
     * @JMS\Type("integer")
     */
    protected $time = 0;

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }
}
