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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro ticket importer
 *
 * Class Ticket
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Ticket extends AbstractImporter
{
    /**
     * @var DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * Constructor
     *
     * @param Mapper\Collection  $mappers
     * @param DeskproBlobStorage $blob_storage
     */
    public function __construct(Mapper\Collection $mappers, DeskproBlobStorage $blob_storage)
    {
        parent::__construct($mappers);
        $this->blob_storage = $blob_storage;
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
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        if (!$importing_entity instanceof Entity\Ticket) {
            throw new \Exception(sprintf(
                'Entity `%s` is not supported by importer `%s`',
                get_class($importing_entity), get_class($this)
            ));
        }

        $this->records = new ArrayCollection();
        $ticket = new DeskPROEntity\Ticket();
        $ticket
            ->setSubject($importing_entity->getSubject())
            ->setPerson($this->getPersonMapper()->findOneByEmail($importing_entity->getPersonEmail()))
            ->setOrganization($this->findOrCreateOrganization($importing_entity->getOrganization()))
            ->setLanguageId($this->findLanguageId($importing_entity->getLanguage()))
            ->setDepartment($this->findOrCreateTicketDepartment($importing_entity->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($importing_entity->getPriority()))
            ->setCategory($this->findOrCreateTicketCategory($importing_entity->getCategory()))
            ->setStatus($importing_entity->getStatus())
            ->setDateCreated($importing_entity->getDateCreated())
            ->setDateResolved($importing_entity->getDateResolved())
            ->setDateArchived($importing_entity->getDateArchived())
            ->resetMessages()
            ->resetParticipants()
        ;

        if ($importing_entity->getAgentEmail()) {
            $this->logInfo(sprintf('Applying ticket agent `%s`', $importing_entity->getAgentEmail()));
            $agent = $this->getPersonMapper()->findOneByEmail($importing_entity->getAgentEmail());
            if (!$agent->isAgent()) {
                throw new ImporterException(sprintf('Person `%s` is not an agent', $agent->getEmailAddress()));
            }
        }
        foreach ($importing_entity->getMessages() as $importing_message) {
            $ticket->addMessage($this->getDoctrineMessageEntity($importing_message));
        }

        $this->records->add($ticket);
        return $this->records;
    }

    /**
     * Returns the importing DeskPro doctrine ticket message entity
     *
     * @param Entity\TicketMessage $importing_entity
     * @return DeskPROEntity\TicketMessage
     */
    private function getDoctrineMessageEntity(Entity\TicketMessage $importing_entity)
    {
        $message = new DeskPROEntity\TicketMessage();
        $message
            ->setPersonId($this->getPersonMapper()->findOneByEmail($importing_entity->getPersonEmail())->getId())
            ->setDateCreated($importing_entity->getDateCreated());

        if ($importing_entity->getMessageText()) {
            $message->setMessageText($importing_entity->getMessageText());
        }
        if ($importing_entity->getMessageHtml()) {
            $message->setMessageText($importing_entity->getMessageHtml());
        }
        foreach ($importing_entity->getAttachments() as $importing_attachment) {
            $message->addAttachment(
                $this->getDoctrineAttachmentEntity(
                    $importing_attachment,
                    $importing_entity->getPersonEmail()
                )
            );
        }

        return $message;
    }

    /**
     * Returns the importing DeskPro doctrine ticket message attachment entity
     *
     * @param Entity\TicketAttachment $importing_entity
     * @param string                  $message_person_email
     *
     * @return DeskPROEntity\TicketAttachment
     */
    private function getDoctrineAttachmentEntity(Entity\TicketAttachment $importing_entity, $message_person_email)
    {
        $person_email = $importing_entity->getPersonEmail() ? : $message_person_email;
        $attachment   = new DeskPROEntity\TicketAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($person_email))
            ->setBlob($this->getBlobData($importing_entity));

        return $attachment;
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
    protected function findOrCreateTicketDepartment($title)
    {
        $department = null;
        if ($title) {
            $department = $this->getTicketDepartmentMapper()->findOneByTitle($title, false);
            if ($department) {
                $this->logInfo(sprintf(
                    'Found existing department `%d` with title `%s`',
                    $department->getId(), $department->getTitle()
                ));
            } else {
                $department = DeskPROEntity\Department::createTicketDepartment();
                $department->setRealTitle($title);

                $this->records->add($department);
                $this->logWarning(sprintf('New department creating `%s`', $department->getTitle()));
            }
        }

        return $department;
    }

    /**
     * Returns a ticket priority by title
     * Creates a new ticket priority if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\TicketPriority|null
     * @throws \Exception
     */
    private function findOrCreateTicketPriority($title)
    {
        $priority = null;
        if ($title) {
            $priority = $this->getTicketPriorityMapper()->findOneByTitle($title, false);
            if ($priority) {
                $this->logInfo(sprintf('Found existing ticket priority `%s`', $priority->getTitle()));
            } else {
                $priority = new DeskPROEntity\TicketPriority();
                $priority->setRealTitle($title);

                $this->records->add($priority);
                $this->logWarning(sprintf('New ticket priority creating `%s`', $priority->getTitle()));
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
                $this->logInfo(sprintf('Found existing ticket category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\TicketCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New ticket priority creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * @param Entity\TicketAttachment $importing_entity
     *
     * @return DeskPROEntity\Blob
     * @throws \Exception
     */
    private function getBlobData(Entity\TicketAttachment $importing_entity)
    {
        if (!$importing_entity->getBlobData() &&  !$importing_entity->getBlobPath() && !$importing_entity->getBlobUrl()) {
            throw new \Exception(sprintf("Invalid Attachment: The attachement must have one of 'blob_data', 'blob_path' or 'blob_url'"));
        }

        $blob_data = null;
        if ($importing_entity->getBlobData()) {
            $blob_data = base64_decode($importing_entity->getBlobData());

        } elseif ($importing_entity->getBlobPath()) {
            if (!is_readable($importing_entity->getBlobPath())) {
                throw new \Exception(sprintf('Invalid blob path %s', $importing_entity->getBlobPath()));
            }

            $blob_data = file_get_contents($importing_entity->getBlobPath());

        } elseif ($importing_entity->getBlobUrl()) {
            if (!is_readable($importing_entity->getBlobUrl())) {
                throw new \Exception(sprintf('Invalid blob url %s', $importing_entity->getBlobUrl()));
            }

            $blob_data = file_get_contents($importing_entity->getBlobUrl());
        }

        return $this->blob_storage->createBlobRecordFromString($blob_data, $importing_entity->getFileName(), $importing_entity->getContentType());
    }

    /**
     * Returns the ticket mapper
     *
     * @return Mapper\Ticket
     * @throws \Exception
     */
    private function getTicketMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET);
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
}
