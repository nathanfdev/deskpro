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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Organization;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Organization.
 */
class Organization
{
    /**
     * The unique organization ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The organization name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Short organization description.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $summary;

    /**
     * Organization importance.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $importance;

    /**
     * Custom organization fields.
     *
     * @var CustomDataAbstract[]
     *
     * @JMS\Type("deferred<custom_data<map<Application\DeskPRO\Entity\CustomDataAbstract>>>")
     */
    protected $fields;

    /**
     * Usergroups associated with this organization.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $userGroups;

    /**
     * Labels associated with this organization.
     *
     * @JMS\Type("deferred<array<label<Application\DeskPRO\Entity\LabelOrganization>>>")
     *
     * @var \Application\DeskPRO\Entity\Labels\Label[]
     */
    protected $labels;

    /**
     * Organization contacts.
     *
     * @JMS\Type("deferred<collection>")
     *
     * @var \Application\DeskPRO\Entity\OrganizationContactData[]
     */
    protected $contactData;

    /**
     * Organization email domains.
     *
     * @JMS\Type("deferred<array<to_string<Application\DeskPRO\Entity\OrganizationEmailDomain>>>")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $emailDomains;

    /**
     * Date when this organization was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Organization parent (this is organization too).
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     *
     * @var \Application\DeskPRO\Entity\Organization|null
     */
    protected $parent;

    /**
     * How many chats organization participating.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $chatsCount;

    /**
     * How many tickets organization participating.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $ticketsCount;

    /**
     * Constructor.
     *
     * @param OrganizationEntity $organization
     * @param int                $chatsCount
     */
    public function __construct(OrganizationEntity $organization, $chatsCount)
    {
        $this->id           = $organization->getId();
        $this->name         = $organization->getName();
        $this->summary      = $organization->getSummary();
        $this->importance   = $organization->getImportance();
        $this->userGroups   = $organization->getPublicUsergroups();
        $this->dateCreated  = $organization->getDateCreated();
        $this->parent       = $organization->getParent();
        $this->chatsCount   = $chatsCount;
        $this->ticketsCount = $organization->getTicketsCount();
    }

    /**
     * @param CallbackDeferredProperty $customData
     *
     * @return $this
     */
    public function setCustomData($customData = null)
    {
        $this->fields = $customData;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $contactData
     *
     * @return $this
     */
    public function setContactData($contactData = null)
    {
        $this->contactData = $contactData;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $labels
     *
     * @return $this
     */
    public function setLabels($labels = null)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $emailDomains
     *
     * @return $this
     */
    public function setEmailDomains($emailDomains = null)
    {
        $this->emailDomains = $emailDomains;

        return $this;
    }
}
