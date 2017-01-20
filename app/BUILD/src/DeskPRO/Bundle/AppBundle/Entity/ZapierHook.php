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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ZapierHook.
 *
 * @ORM\Entity()
 * @ORM\Table(name="zapier_hooks")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ZapierHook implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="target_url", type="string", length=255, nullable=true)
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $targetUrl;

    /**
     * @ORM\Column(name="event", type="string", length=255, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $event;

    /**
     * Unused legacy field.
     *
     * @var string
     */
    private $subscriptionUrl;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ZapierHook
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * @param string $targetUrl
     *
     * @return ZapierHook
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setModelField('targetUrl', $targetUrl);

        return $this;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $event
     *
     * @return ZapierHook
     */
    public function setEvent($event)
    {
        $this->setModelField('event', $event);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscriptionUrl()
    {
        return $this->subscriptionUrl;
    }

    /**
     * @param string $subscriptionUrl
     *
     * @return ZapierHook
     */
    public function setSubscriptionUrl($subscriptionUrl)
    {
        $this->subscriptionUrl = $subscriptionUrl;

        return $this;
    }
}
