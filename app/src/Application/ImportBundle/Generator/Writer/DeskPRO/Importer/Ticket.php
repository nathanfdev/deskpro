<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\DeskPRO\Tickets\TicketManager;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro ticket importer
 *
 * Class Ticket
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer
 */
final class Ticket extends AbstractImporter
{
    /**
     * @var TicketManager
     */
    private $manager;

    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor
     *
     * @param Mapper\Collection    $mappers
     * @param TicketManager        $manager
     * @param BlobAdapterInterface $blob_adapter
     */
    public function __construct(Mapper\Collection $mappers, TicketManager $manager, BlobAdapterInterface $blob_adapter)
    {
        parent::__construct($mappers);

        $this->manager      = $manager;
        $this->blob_adapter = $blob_adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Ticket $entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        $ticket = $this->findOrCreateTicket($entity);
        $ticket
            ->disableAutoTicketProcess()
            ->setSubject($entity->getSubject())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setOrganization($this->findOrCreateOrganization($entity->getOrganization()))
            ->setLanguageId($this->findLanguageId($entity->getLanguage()))
            ->setDepartment($this->findOrCreateTicketDepartment($entity->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($entity->getPriority()))
            ->setCategory($this->findOrCreateTicketCategory($entity->getCategory()))
            ->setStatus($entity->getStatus())
            ->setDateCreated($entity->getDateCreated())
            ->setDateResolved($entity->getDateResolved())
            ->setDateArchived($entity->getDateArchived())
            ->resetMessages()
            ->resetParticipants()
            ->resetLabels()
            ->resetCustomData()
        ;

        if ($entity->getAgentEmail()) {
            $ticket->setAgentId($this->getPersonMapper()->findOneByEmail($entity->getAgentEmail())->getId());
        }
        foreach ($entity->getMessages() as $message) {
            $ticket->addMessage($this->createTicketMessage($message, $ticket));
        }
        foreach ($entity->getParticipants() as $participant) {
            $ticket->addParticipant($this->createParticipant($participant));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createCustomData($custom_field);
            if ($custom_field) {
                $ticket->addCustomData($custom_field);
            }
        }

        $this->records->add($ticket);
        return $this->records;
    }

    /**
     * Returns a ticket entity
     * Creates a new ticket if not found
     *
     * @param Entity\Ticket $entity
     *
     * @return DeskPROEntity\Ticket
     * @throws \Exception
     */
    private function findOrCreateTicket(Entity\Ticket $entity)
    {
        $ticket = $this->getTicketMapper()->findOneByRef($entity->getRef(), false);
        if ($ticket) {
            $this->logDebug(sprintf(
                'Found existing ticket, id=`%d` with ref `%s`',
                $ticket->getId(), $ticket->getRef()
            ));
        } else {
            $ticket = new DeskPROEntity\Ticket();
            $ticket->setRef($entity->getRef());

            $this->logInfo(sprintf('Creating new ticket with ref `%s`', $entity->getRef()));

            $ticket_log = new DeskPROEntity\TicketLog();
            $ticket_log
                ->setTicket($ticket)
                ->setActionType('free')
                ->setDetails(array(
                    'message' => $entity->getLogMessage() ? : sprintf('Imported (old ticket ID #%s)', $entity->getOid()),
                ))
            ;

            $this->records->add($ticket_log);
        }

        return $ticket;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message entity
     * We should pass ticket entity due to set attachment ticket_id field
     *
     * @param Entity\TicketMessage $entity
     * @param DeskPROEntity\Ticket $ticket
     *
     * @return DeskPROEntity\TicketMessage
     */
    private function createTicketMessage(Entity\TicketMessage $entity, DeskPROEntity\Ticket $ticket)
    {
        $message = new DeskPROEntity\TicketMessage();
        $message
            ->setTicket($ticket)
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setDateCreated($entity->getDateCreated())
            ->setAsAgentNote($entity->isNote())
        ;

        if ($entity->getMessageText()) {
            $message->setMessageText($entity->getMessageText());
        }
        if ($entity->getMessageHtml()) {
            $message->setMessageHtml($entity->getMessageHtml());
        }
        foreach ($entity->getAttachments() as $attachment) {
            $message->addAttachment($this->createAttachment($attachment, $entity->getPersonEmail()));
        }

        $this->records->add($message);
        return $message;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message attachment entity
     *
     * @param Entity\Attachment $entity
     * @param string            $person_email
     *
     * @return DeskPROEntity\TicketAttachment
     */
    private function createAttachment(Entity\Attachment $entity, $person_email)
    {
        $email = $entity->getPersonEmail() ? : $person_email;
        $attachment = new DeskPROEntity\TicketAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($email))
            ->setBlob($this->blob_adapter->createByAttachment($entity))
        ;

        return $attachment;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket participant entity
     *
     * @param string $email
     *
     * @return DeskPROEntity\TicketParticipant
     * @throws Mapper\MapperException
     */
    private function createParticipant($email)
    {
        $participant = new DeskPROEntity\TicketParticipant();
        $participant->setPerson($this->getPersonMapper()->findOneByEmail($email));

        $this->records->add($participant);
        return $participant;
    }

    /**
     * Returns a department by title
     * Creates a new department if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\Department|null
     * @throws \Exception
     */
    private function findOrCreateTicketDepartment($title)
    {
        $department = null;
        if ($title) {
            $department = $this->getTicketDepartmentMapper()->findOneByTitle($title, false);
            if ($department) {
                $this->logDebug(sprintf(
                    'Found existing department `%d` with title `%s`',
                    $department->getId(), $department->getTitle()
                ));
            } else {
                $department = DeskPROEntity\Department::createTicketDepartment();
                $department->setRealTitle($title);

                $this->records->add($department);
                $this->logNotice(sprintf('New department creating `%s`', $department->getTitle()));
            }
        }

        return $department;
    }

    /**
     * Returns a ticket priority by title
     * Creates a new ticket priority if not found
     *
     * @param Entity\TicketPriority $entity
     *
     * @return DeskPROEntity\TicketPriority|null
     * @throws \Exception
     */
    private function findOrCreateTicketPriority(Entity\TicketPriority $entity = null)
    {
        $priority = null;
        if ($entity) {
            $priority = $this->getTicketPriorityMapper()->findOneByTitle($entity->getTitle(), false);
            if ($priority) {
                $this->logDebug(sprintf('Found existing ticket priority `%s`', $priority->getTitle()));
            } else {
                $priority = new DeskPROEntity\TicketPriority();
                $priority
                    ->setRealTitle($entity->getTitle())
                    ->setPriority($entity->getValue());

                $this->records->add($priority);
                $this->logNotice(sprintf('New ticket priority creating `%s`', $priority->getTitle()));
            }
        }

        return $priority;
    }

    /**
     * Returns a ticket category by title
     * Creates a new ticket category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\TicketCategory|null
     * @throws \Exception
     */
    private function findOrCreateTicketCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getTicketCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logDebug(sprintf('Found existing ticket category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\TicketCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logInfo(sprintf('New ticket category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns custom def person entity
     *
     * @param Entity\CustomField $entity
     *
     * @return DeskPROEntity\CustomDataTicket
     * @throws ImporterException
     */
    private function createCustomData(Entity\CustomField $entity)
    {
        $custom_field = new DeskPROEntity\CustomDataTicket();
        $mapper       = $this->getCustomDefTicketMapper();
        $ticket_def   = $mapper->findOneBy(array(
            'title'  => $entity->getKey(),
            'parent' => null,
        ));

        switch ($ticket_def->getTypeName()) {
            case Entity\CustomField::FIELD_TYPE_TEXT:
            case Entity\CustomField::FIELD_TYPE_TEXTAREA:
                $custom_field
                    ->setField($ticket_def)
                    ->setRootField($ticket_def)
                    ->setInput($entity->getValue())
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_TOGGLE:
                $custom_field
                    ->setField($ticket_def)
                    ->setRootField($ticket_def)
                    ->setValue($entity->getValue() ? 1 : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_DATE:
            case Entity\CustomField::FIELD_TYPE_DATETIME:
                $custom_field
                    ->setField($ticket_def)
                    ->setRootField($ticket_def)
                    ->setValue($entity->getValue() ? strtotime($entity->getValue()) : 0)
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_CHOICE:
                /** @var DeskPROEntity\CustomDefTicket $choice */
                $choice = $this->findChoiceCustomDef($mapper, $entity->getValue(), $ticket_def);
                $custom_field
                    ->setField($choice)
                    ->setRootField($ticket_def)
                    ->setValue(1)
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_DISPLAY:

                // todo implement

                break;

            case Entity\CustomField::FIELD_TYPE_HIDDEN:

                // todo implement

                break;

            default:
                throw new ImporterException('Unknown custom field type `%s`', $ticket_def->getTypeName());
        }

        $this->records->add($custom_field);
        return $custom_field;
    }

    /**
     * Returns the ticket department mapper
     *
     * @return Mapper\TicketDepartment
     * @throws \Exception
     */
    private function getTicketDepartmentMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_DEPARTMENT);
    }

    /**
     * Returns the ticket priority mapper
     *
     * @return Mapper\TicketPriority
     * @throws \Exception
     */
    private function getTicketPriorityMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_PRIORITY);
    }

    /**
     * Returns the ticket category mapper
     *
     * @return Mapper\TicketCategory
     * @throws \Exception
     */
    private function getTicketCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_CATEGORY);
    }

    /**
     * Returns the custom def person mapper
     *
     * @return Mapper\CustomDefTicket
     * @throws \Exception
     */
    private function getCustomDefTicketMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_CUSTOM_DEF_TICKET);
    }
}
