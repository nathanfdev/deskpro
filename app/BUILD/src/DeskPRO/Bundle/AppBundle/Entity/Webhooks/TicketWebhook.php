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

namespace DeskPRO\Bundle\AppBundle\Entity\Webhooks;

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Application\DeskPRO\EntityRepository\AppInstance;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Webhooks\Repository")
 * @ORM\Table(
 *  name="ticket_webhooks", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="auth_id_unique", columns={"auth_id"})
 *  })
 * @JMS\ExclusionPolicy("all")
 */
class TicketWebhook
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Expose()
     */
    private $id;

    /**
     * The URL slug, a string of random uppercase characters
     *
     * @ORM\Column(name="auth_id", type="string", nullable=false)
     * @JMS\Expose()
     * @var  string
     */
    private $authId;

    /**
     * A title / description the admin gives it
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     * @JMS\Expose()
     * @var string
     */
    private $title;

    /**
     * Payload decoding strategy
     *
     * @ORM\Column(name="payload_decoder", type="string", nullable=false)
     * @JMS\Expose()
     * @var string
     */
    private $payloadDecoder;

    /**
     *  True for the webhook is enabled or not
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=false)
     * @JMS\Expose()
     * @var boolean
     */
    private $isEnabled;

    /**
     * @ORM\Column(name="search_terms", type="json_array", nullable=true)
     * @JMS\Expose()
     * @var array
     *
     */
    private $searchTerms;

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\TicketTrigger", cascade={"persist"})
     * @ORM\JoinTable(name="ticket_webhook_triggers",
     *      joinColumns={@ORM\JoinColumn(name="webhook_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="trigger_id", referencedColumnName="id", unique=true)}
     *      )
     * @JMS\Expose()
     * @JMS\Type("array<Application\DeskPRO\Entity\TicketTrigger>")
     * @var TicketTrigger[]
     */
    private $triggers;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance")
     * @ORM\JoinColumn(name="app_instance_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @JMS\Expose()
     *
     * @var AppInstance
     */
    private $appInstance;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AppInstance
     */
    public function getAppInstance()
    {
        return $this->appInstance;
    }

    /**
     * @param AppInstance $appInstance
     */
    public function setAppInstance( $appInstance )
    {
        $this->appInstance = $appInstance;
    }

    /**
     * @return \Application\DeskPRO\Tickets\Filters\FilterTerms
     */
    public function getSearchTerms()
    {
        if ($this->searchTerms) {
            $trans             = new LegacyTermsTransformer();
            return $trans->toFilterTerms($this->searchTerms);
        }
    }

    /**
     * @param \Application\DeskPRO\Tickets\Filters\FilterTerms|array $searchTerms
     */
    public function setSearchTerms( $searchTerms )
    {
        if ($searchTerms instanceof FilterTerms) {
            $trans             = new LegacyTermsTransformer();
            $this->searchTerms = $trans->toLegacyTerms($searchTerms);
        } else if (is_array($searchTerms)) {
            $this->searchTerms = $searchTerms;
        } else {
            throw new \BadMethodCallException('invalid parameter type');
        }
    }

    /**
     * @return TicketTrigger[]|ArrayCollection
     */
    public function getTriggers()
    {
        return $this->triggers;
    }

    /**
     * @param ArrayCollection $triggers
     */
    public function setTriggers($triggers)
    {
        $this->triggers = $triggers;
    }

    /**
     * @return bool
     */
    public function isIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     */
    public function setIsEnabled( $isEnabled )
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return string
     */
    public function getPayloadDecoder()
    {
        return $this->payloadDecoder;
    }

    /**
     * @param string $payloadDecoder
     */
    public function setPayloadDecoder( $payloadDecoder )
    {
        $this->payloadDecoder = $payloadDecoder;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAuthId()
    {
        return $this->authId;
    }

    /**
     * @param string $authId
     */
    public function setAuthId( $authId )
    {
        $this->authId = $authId;
    }
}
