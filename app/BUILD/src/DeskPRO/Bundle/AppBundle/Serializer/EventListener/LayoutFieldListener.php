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

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutField;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * Class LayoutFieldListener.
 */
class LayoutFieldListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketFieldSettings
     */
    private $fieldSettings;

    /**
     * @var array
     */
    private $customFields = [];

    /**
     * Constructor.
     *
     * @param EntityManager       $em
     * @param TicketFieldSettings $fieldSettings
     */
    public function __construct(EntityManager $em, TicketFieldSettings $fieldSettings)
    {
        $this->em            = $em;
        $this->fieldSettings = $fieldSettings;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::PRE_SERIALIZE,
                'method' => 'onSetRequired',
                'class'  => LayoutField::class,
            ],
        ];
    }

    /**
     * @internal
     *
     * @param ObjectEvent $event
     */
    public function onSetRequired(ObjectEvent $event)
    {
        /** @var LayoutField $model */
        $model = $event->getObject();

        switch ($model->getFieldType()) {
            case FormFields::SUBJECT:
            case FormFields::DEPARTMENT:
            case FormFields::MESSAGE:
                $model->setRequired(true);
                break;
            case FormFields::CATEGORY:
                $model->setRequired($this->fieldSettings->isCategoryRequired($model->isAgent()));
                break;
            case FormFields::PRIORITY:
                $model->setRequired($this->fieldSettings->isPriorityRequired($model->isAgent()));
                break;
            case FormFields::WORKFLOW:
                $model->setRequired($this->fieldSettings->isWorkflowRequired($model->isAgent()));
                break;
            case FormFields::PRODUCT:
                $model->setRequired($this->fieldSettings->isProductRequired($model->isAgent()));
                break;
            case FormFields::TICKET_FIELD:
                $model->setRequired($this->isCustomFieldRequired($model, CustomDefTicket::class));
                break;
            case FormFields::USER_FIELD:
                $model->setRequired($this->isCustomFieldRequired($model, CustomDefPerson::class));
                break;
            case FormFields::ORG_FIELD:
                $model->setRequired($this->isCustomFieldRequired($model, CustomDefOrganization::class));
                break;
            default:
                $model->setRequired(false);
                break;
        }
    }

    /**
     * @param LayoutField $model
     * @param string      $entityClass
     *
     * @return bool
     */
    private function isCustomFieldRequired($model, $entityClass)
    {
        if (!isset($this->customFields[$entityClass])) {
            $this->customFields[$entityClass] = [];

            /** @var CustomDefAbstract[] $fields */
            $fields = $this->em->getRepository($entityClass)->findBy(['parent' => null]);
            foreach ($fields as $field) {
                $this->customFields[$entityClass][$field->getId()] = $field;
            }
        }

        if (isset($this->customFields[$entityClass][$model->getOriginalFieldId()])) {
            /** @var CustomDefAbstract $field */
            $field = $this->customFields[$entityClass][$model->getOriginalFieldId()];

            return $field->isRequired($model->isAgent());
        }

        return false;
    }
}
