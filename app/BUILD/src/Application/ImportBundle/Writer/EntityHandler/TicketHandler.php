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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\DeskPRO\Tickets\TicketManager;
use Application\ImportBundle\Model;
use Application\ImportBundle\Writer\Helper\BlobAdapter;
use Application\ImportBundle\Writer\Helper\CustomDataHelper;
use Application\ImportBundle\Writer\Helper\LabelHelper;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Application\ImportBundle\Writer\Mapper\OidEntityMap;
use Psr\Log\LoggerInterface;

/**
 * DeskPRO ticket importer.
 *
 * Class Ticket
 */
class TicketHandler extends AbstractEntityHandler
{
    /**
     * @var TicketManager
     */
    private $manager;

    /**
     * @var BlobAdapter
     */
    private $blob_adapter;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param LoggerInterface $logger
     * @param TicketManager   $manager
     * @param BlobAdapter     $blob_adapter
     */
    public function __construct(MapperRegistry $mappers, LoggerInterface $logger, TicketManager $manager, BlobAdapter $blob_adapter)
    {
        parent::__construct($mappers, $logger);

        $this->manager      = $manager;
        $this->blob_adapter = $blob_adapter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Ticket::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Ticket $model
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        $entity = $this->findOrCreateTicket($model, $entityId);
        $entity
            ->disableAutoTicketProcess()
            ->setRef($model->getRef())
            ->setSubject($model->getSubject())
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($model->getPerson()))
            ->setOrganization($this->findOrCreateOrganization($model->getOrganization()))
            ->setDepartment($this->findOrCreateTicketDepartment($model->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($model->getPriority()))
            ->setCategory($this->findOrCreateTicketCategory($model->getCategory()))
            ->setStatus($model->getStatus())
            ->setLanguage($model->getLanguage() ? $this->findLanguage($model->getLanguage()) : null)
            ->setDateCreated($model->getDateCreated())
            ->setDateResolved($model->getDateResolved())
            ->setDateArchived($model->getDateArchived())
            ->setIsHold($model->isHold())
            ->resetParticipants()
        ;

        if ($model->getAgent()) {
            $agent = $this->mappers->getPersonMapper()->findOneByEmail($model->getAgent());

            if ($agent && $agent->isAgent()) {
                $entity->setAgent($agent);
            } else {
                $this->logger->warning(sprintf('Unable to set ticket agent, `%s` is not an agent', $model->getAgent()));
                $entity->setAgent(null);
            }
        } else {
            $entity->setAgent(null);
        }

        foreach ($model->getMessages() as $message) {
            $exist_message = $this->mappers->getTicketMessageMapper()->findOneBy(['entity' => $message], false);
            if ($exist_message) {
                $this->logger->debug(sprintf('Found existing ticket message by oid=`%d`', $message->getOid()));
                $this->updateTicketMessage($message, $exist_message);
            } else {
                $this->logger->debug(sprintf('Creating a new ticket message oid=`%d`', $message->getOid()));
                $entity->addMessage($this->createTicketMessage($message, $entity));
            }
        }

        foreach ($model->getParticipants() as $participant) {
            $entity->addParticipant($this->createParticipant($participant));
        }

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelTicket::class);

        $customDataHelper = new CustomDataHelper($this->mappers->getTicketCustomDefMapper(), $this->logger);
        $customDataHelper->updateCustomData($model, $entity, $this->records);

        $this->records->setPrimaryEntity($entity);
    }

    /**
     * Returns a ticket entity.
     * Creates a new ticket if not found.
     *
     * @param Model\Ticket $entity
     * @param int          $entity_id
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\Ticket
     */
    private function findOrCreateTicket(Model\Ticket $entity, $entity_id)
    {
        $ticket = $this->mappers->getTicketMapper()->findOneBy(['ref' => $entity->getRef()], false);
        if ($ticket) {
            $this->logger->debug(sprintf(
                'Found existing ticket by ref, id=`%d` with ref `%s`',
                $ticket->getId(), $ticket->getRef()
            ));
        } else {
            $ticket = $this->mappers->getTicketMapper()->findOneBy(['id' => $entity_id], false);
            if ($ticket) {
                $this->logger->debug(sprintf(
                    'Found existing ticket by import map, id=`%d` with ref `%s`',
                    $ticket->getId(), $ticket->getRef()
                ));
            }
        }

        if (!$ticket) {
            $ticket = new DeskPROEntity\Ticket();
            $this->logger->info(sprintf('Creating new ticket with ref `%s`', $entity->getRef()));

            $ticket_log = new DeskPROEntity\TicketLog();
            $ticket_log
                ->setTicket($ticket)
                ->setActionType('free')
                ->setDetails([
                    'message' => $entity->getLogMessage() ?: sprintf('Imported (old ticket ID #%s)', $entity->getOid()),
                ])
            ;

            $this->records->addRelatedEntity($ticket_log);
        }

        return $ticket;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message entity
     * We should pass ticket entity due to set attachment ticket_id field.
     *
     * @param Model\TicketMessage  $entity
     * @param DeskPROEntity\Ticket $ticket
     *
     * @return DeskPROEntity\TicketMessage
     */
    private function createTicketMessage(Model\TicketMessage $entity, DeskPROEntity\Ticket $ticket)
    {
        $message = new DeskPROEntity\TicketMessage();
        $message->setTicket($ticket);

        $this->updateTicketMessage($entity, $message);
        $this->records->addImportMapEntity(new OidEntityMap($entity, $message));

        return $message;
    }

    /**
     * Update ticket message.
     *
     * @param Model\TicketMessage         $entity
     * @param DeskPROEntity\TicketMessage $message
     *
     * @return DeskPROEntity\TicketMessage
     */
    private function updateTicketMessage(Model\TicketMessage $entity, DeskPROEntity\TicketMessage $message)
    {
        $message
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($entity->getPerson()))
            ->setDateCreated($entity->getDateCreated())
            ->setAsAgentNote($entity->isNote())
        ;

        if ($entity->getMessage()) {
            $message->setMessageText($entity->getMessage());
        }
        if ($entity->getFormat()) {
            $message->setMessageHtml($entity->getFormat());
        }
        foreach ($entity->getAttachments() as $attachment) {
            $message->addAttachment($this->createAttachment($attachment, $entity->getPerson()));
        }

        $this->records->addRelatedEntity($message);

        return $message;
    }

    /**
     * Returns the importing DeskPRO doctrine ticket message attachment entity.
     *
     * @param Model\Attachment $entity
     * @param string           $person_email
     *
     * @return DeskPROEntity\TicketAttachment
     */
    private function createAttachment(Model\Attachment $entity, $person_email)
    {
        $email = $entity->getPerson() ?: $person_email;

        $attachment = new DeskPROEntity\TicketAttachment();
        $attachment
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($email))
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
        $participant->setPerson($this->mappers->getPersonMapper()->findOneByEmail($email));

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
            $department = $this->mappers->getDepartmentMapper()->findOneByTitle($title, false);
            if ($department) {
                $this->logger->debug(sprintf(
                    'Found existing department `%d` with title `%s`',
                    $department->getId(), $department->getTitle()
                ));
            } else {
                $department = DeskPROEntity\Department::createTicketDepartment();
                $department->setRealTitle($title);

                $this->records->addRelatedEntity($department);
                $this->logger->notice(sprintf('New department creating `%s`', $department->getTitle()));
            }
        }

        return $department;
    }

    /**
     * Returns a ticket priority by title
     * Creates a new ticket priority if not found.
     *
     * @param Model\TicketPriority $entity
     *
     * @return DeskPROEntity\TicketPriority|null
     */
    private function findOrCreateTicketPriority(Model\TicketPriority $entity = null)
    {
        $priority = null;
        if ($entity) {
            $priority = $this->mappers->getTicketPriorityMapper()->findOneByTitle($entity->getTitle(), false);
            if ($priority) {
                $this->logger->debug(sprintf('Found existing ticket priority `%s`', $priority->getTitle()));
            } else {
                $priority = new DeskPROEntity\TicketPriority();
                $priority
                    ->setRealTitle($entity->getTitle())
                    ->setPriority($entity->getValue())
                ;

                $this->records->addRelatedEntity($priority);
                $this->logger->notice(sprintf('New ticket priority creating `%s`', $priority->getTitle()));
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
            $category = $this->mappers->getTicketCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logger->debug(sprintf('Found existing ticket category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\TicketCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logger->info(sprintf('New ticket category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }
}
