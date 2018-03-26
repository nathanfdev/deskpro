<?php

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
