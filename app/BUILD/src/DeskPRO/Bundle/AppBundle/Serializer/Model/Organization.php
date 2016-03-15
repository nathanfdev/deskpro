<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\CustomDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Organization.
 */
class Organization
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var int
     */
    protected $importance;

    /**
     * @var CustomDataAbstract[]
     *
     * @JMS\Type("custom_data<array>")
     */
    protected $fields;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     */
    protected $usergroups;

    /**
     * @var \Application\DeskPRO\Entity\Labels\Label[]
     *
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\LabelOrganization>>")
     */
    protected $labels;

    /**
     * @var \Application\DeskPRO\Entity\OrganizationContactData[]
     *
     * @JMS\Type("collection")
     */
    protected $contact_data;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\OrganizationEmailDomain>>")
     */
    protected $email_domains;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \Application\DeskPRO\Entity\Organization|null
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     */
    protected $parent;

    /**
     * @var int
     */
    protected $chats_count;

    /**
     * @var int
     */
    protected $tickets_count;

    /**
     * @var int
     */
    protected $employees_count;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\Organization $organization
     * @param int                                      $chats_count
     */
    public function __construct(\Application\DeskPRO\Entity\Organization $organization, $chats_count)
    {
        $this->id              = $organization->getId();
        $this->name            = $organization->getName();
        $this->summary         = $organization->getSummary();
        $this->importance      = $organization->getImportance();
        $this->fields          = $organization->getCustomData();
        $this->usergroups      = $organization->getUsergroups();
        $this->labels          = $organization->getLabels();
        $this->contact_data    = $organization->getContactData();
        $this->email_domains   = $organization->getEmailDomains();
        $this->date_created    = $organization->getDateCreated();
        $this->parent          = $organization->getParent();
        $this->chats_count     = $chats_count;
        $this->tickets_count   = $organization->getTicketsCount();
        $this->employees_count = $organization->getEmployeesCount();
    }
}
