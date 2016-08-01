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

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * A service responsible for making sense of "Fields". Usually, special strings (see FormFields class), need to be
 * expanded into more information or fetched from the database.
 */
class CustomFieldManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return CustomDefPerson[]
     */
    public function getAvailablePersonDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefPerson::class);
    }

    /**
     * @return CustomDefFeedback[]
     */
    public function getAvailableFeedbackDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefFeedback::class);
    }

    /**
     * @return CustomDefOrganization[]
     */
    public function getAvailableOrganizationDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefOrganization::class);
    }

    /**
     * @return CustomDefTicket[]
     */
    public function getAvailableTicketDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefTicket::class);
    }

    /**
     * @return CustomDefChat[]
     */
    public function getAvailableChatDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefChat::class);
    }

    /**
     * @param LayoutField $layoutField
     *
     * @return CustomDefAbstract|null
     */
    public function getCustomDefForLayoutField(LayoutField $layoutField)
    {
        $fieldId = $layoutField->getFieldId();

        switch ($layoutField->getFieldType()) {
            case FormFields::TICKET_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            case FormFields::USER_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            case FormFields::ORG_FIELD:
                return $this->getCustomTicketFieldById($fieldId);
            default:
                return false;
        }
    }

    /**
     * @param $id
     *
     * @return CustomDefTicket
     */
    public function getCustomTicketFieldById($id)
    {
        return $this->em->getRepository(CustomDefTicket::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefPerson
     */
    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository(CustomDefPerson::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefOrganization
     */
    public function getCustomOrganizationFieldById($id)
    {
        return $this->em->getRepository(CustomDefOrganization::class)->find($id);
    }

    /**
     * per-user/per-organization special custom fields.
     *
     * @param $id
     *
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    public function getCustomPerFieldById($id)
    {
        return $this->em->getRepository(CustomFieldDefinition::class)->find($id);
    }

    /**
     * @param string $entityType
     *
     * @return CustomDefAbstract[]
     */
    private function getAvailableCustomDefs($entityType)
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('f')
            ->from($entityType, 'f')
            ->where(
                'f.is_user_enabled = true',
                'f.is_enabled = true',
                'f.handler_class IS NOT NULL'
            )
            ->orderBy('f.display_order')
        ;

        $result = new ArrayCollection($qb->getQuery()->getResult());
        $result = $result->filter(function (CustomDefAbstract $def) {
            if ($def->isChoiceType() && !$def->hasChildren()) {
                return false;
            }

            return true;
        });

        return $result;
    }
}
