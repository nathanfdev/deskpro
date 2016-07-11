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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\DeskPRO\Tickets\TicketManager;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper\OidEntityMap;

/**
 * DeskPRO ticket importer.
 *
 * Class Ticket
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
     * Constructor.
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
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Ticket) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $ticket = $this->findOrCreateTicket($entity, $entity_id);
        $ticket
            ->disableAutoTicketProcess()
            ->setRef($entity->getRef())
            ->setSubject($entity->getSubject())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setOrganization($this->findOrCreateOrganization($entity->getOrganization()))
            ->setDepartment($this->findOrCreateTicketDepartment($entity->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($entity->getPriority()))
            ->setCategory($this->findOrCreateTicketCategory($entity->getCategory()))
            ->setStatus($entity->getStatus())
            ->setLanguage($entity->getLanguage() ? $this->findLanguage($entity->getLanguage()) : null)
            ->setDateCreated($entity->getDateCreated())
            ->setDateResolved($entity->getDateResolved())
            ->setDateArchived($entity->getDateArchived())
            ->setIsHold($entity->isHold())
            ->resetParticipants()
            ->clearLabels();

        if ($entity->getAgentEmail()) {
            $agent = $this->getPersonMapper()->findOneByEmail($entity->getAgentEmail());

            if ($agent && $agent->isAgent()) {
                $ticket->setAgent($agent);
            } else {
                $this->logWarning(sprintf('Unable to set ticket agent, `%s` is not an agent', $entity->getAgentEmail()));
                $ticket->setAgent(null);
            }
        } else {
            $ticket->setAgent(null);
        }

        if (!$entity->getMessages()->hasImportMapKey()) {
            $ticket->resetMessages();
            foreach ($entity->getMessages() as $message) {
                $this->logDebug(sprintf('Creating a new ticket message oid=`%d`', $message->getOid()));
                $ticket->addMessage($this->createTicketMessage($message, $ticket));
            }
        } else {
            foreach ($entity->getMessages() as $message) {
                $exist_message = $this->getTicketMessageMapper()->findOneBy(array('entity' => $message), false);
                if ($exist_message) {
                    $this->logDebug(sprintf('Found existing ticket message by oid=`%d`', $message->getOid()));
                    $this->updateTicketMessage($message, $exist_message);
                } else {
                    $this->logDebug(sprintf('Creating a new ticket message oid=`%d`', $message->getOid()));
                    $ticket->addMessage($this->createTicketMessage($message, $ticket));
                }
            }
        }

        foreach ($entity->getParticipants() as $participant) {
            $ticket->addParticipant($this->createParticipant($participant));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createTicketCustomData($custom_field);
            if ($custom_field) {
                $ticket->addCustomData($custom_field);
            }
        }

        $this->records->setPrimaryEntity($ticket);
    }

    /**
     * Returns a ticket entity.
     * Creates a new ticket if not found.
     *
     * @param Entity\Ticket $entity
     * @param int           $entity_id
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\Ticket
     */
    private function findOrCreateTicket(Entity\Ticket $entity, $entity_id)
    {
        $ticket = $this->getTicketMapper()->findOneBy(array('ref' => $entity->getRef()), false);
        if ($ticket) {
            $this->logDebug(sprintf(
                'Found existing ticket by ref, id=`%d` with ref `%s`',
                $ticket->getId(), $ticket->getRef()
            ));
        } else {
            $ticket = $this->getTicketMapper()->findOneBy(array('id' => $entity_id), false);
            if ($ticket) {
                $this->logDebug(sprintf(
                    'Found existing ticket by import map, id=`%d` with ref `%s`',
                    $ticket->getId(), $ticket->getRef()
                ));
            }
        }

        if (!$ticket) {
            $ticket = new DeskPROEntity\Ticket();
            $this->logInfo(sprintf('Creating new ticket with ref `%s`', $entity->getRef()));

            $ticket_log = new DeskPROEntity\TicketLog();
            $ticket_log
                ->setTicket($ticket)
                ->setActionType('free')
                ->setDetails(array(
                    'message' => $entity->getLogMessage() ?: sprintf('Imported (old ticket ID #%s)', $entity->getOid()),
                ))
            ;

            $this->records->addRelatedEntity($ticket_log);
        }

        return $ticket;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message entity
     * We should pass ticket entity due to set attachment ticket_id field.
     *
     * @param Entity\TicketMessage $entity
     * @param DeskPROEntity\Ticket $ticket
     *
     * @return DeskPROEntity\TicketMessage
     */
    private function createTicketMessage(Entity\TicketMessage $entity, DeskPROEntity\Ticket $ticket)
    {
        $message = new DeskPROEntity\TicketMessage();
        $message->setTicket($ticket);

        $this->updateTicketMessage($entity, $message);

        if ($entity->getImportMapKey()) {
            $this->records->addImportMapEntity(new OidEntityMap($entity, $message));
        }

        return $message;
    }

    /**
     * Update ticket message.
     *
     * @param Entity\TicketMessage        $entity
     * @param DeskPROEntity\TicketMessage $message
     *
     * @return DeskPROEntity\TicketMessage
     */
    private function updateTicketMessage(Entity\TicketMessage $entity, DeskPROEntity\TicketMessage $message)
    {
        $message
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

        $this->records->addRelatedEntity($message);

        return $message;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message attachment entity.
     *
     * @param Entity\Attachment $entity
     * @param string            $person_email
     *
     * @return DeskPROEntity\TicketAttachment
     */
    private function createAttachment(Entity\Attachment $entity, $person_email)
    {
        $email = $entity->getPersonEmail() ?: $person_email;

        $attachment = new DeskPROEntity\TicketAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($email))
            ->setBlob($this->blob_adapter->createByBlob($entity))
        ;

        return $attachment;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket participant entity.
     *
     * @param string $email
     *
     * @return DeskPROEntity\TicketParticipant
     */
    private function createParticipant($email)
    {
        $participant = new DeskPROEntity\TicketParticipant();
        $participant->setPerson($this->getPersonMapper()->findOneByEmail($email));

        $this->records->addRelatedEntity($participant);

        return $participant;
    }

    /**
     * Returns a department by title
     * Creates a new department if not found.
     *
     * @param string $title
     *
     * @return DeskPROEntity\Department|null
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

                $this->records->addRelatedEntity($department);
                $this->logNotice(sprintf('New department creating `%s`', $department->getTitle()));
            }
        }

        return $department;
    }

    /**
     * Returns a ticket priority by title
     * Creates a new ticket priority if not found.
     *
     * @param Entity\TicketPriority $entity
     *
     * @return DeskPROEntity\TicketPriority|null
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
                    ->setPriority($entity->getValue())
                ;

                $this->records->addRelatedEntity($priority);
                $this->logNotice(sprintf('New ticket priority creating `%s`', $priority->getTitle()));
            }
        }

        return $priority;
    }

    /**
     * Returns a ticket category by title
     * Creates a new ticket category if not found.
     *
     * @param string $title
     *
     * @return DeskPROEntity\TicketCategory|null
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

                $this->records->addRelatedEntity($category);
                $this->logInfo(sprintf('New ticket category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns a ticket custom data entity.
     *
     * @param Entity\CustomField $entity
     *
     * @return DeskPROEntity\CustomDataTicket
     */
    private function createTicketCustomData(Entity\CustomField $entity)
    {
        return $this->createCustomData($this->getTicketCustomDefMapper(), $entity, new DeskPROEntity\CustomDataTicket());
    }

    /**
     * Returns the ticket department mapper.
     *
     * @return Mapper\TicketDepartment
     */
    private function getTicketDepartmentMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_DEPARTMENT);
    }

    /**
     * Returns the ticket priority mapper.
     *
     * @return Mapper\TicketPriority
     */
    private function getTicketPriorityMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_PRIORITY);
    }

    /**
     * Returns the ticket category mapper.
     *
     * @return Mapper\TicketCategory
     */
    private function getTicketCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_CATEGORY);
    }

    /**
     * Returns the ticket message mapper.
     *
     * @return Mapper\TicketMessage
     */
    private function getTicketMessageMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_MESSAGE);
    }
}
