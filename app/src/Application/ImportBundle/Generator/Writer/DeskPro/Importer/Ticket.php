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

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Orb\Util\Strings;

/**
 * DeskPro ticket importer
 *
 * Class Ticket
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Ticket extends AbstractImporter
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor
     *
     * @param Mapper\Collection    $mappers
     * @param BlobAdapterInterface $blob_adapter
     */
    public function __construct(Mapper\Collection $mappers, BlobAdapterInterface $blob_adapter)
    {
        parent::__construct($mappers);
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
     * @var Entity\Ticket $importing_entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        $this->records = new ArrayCollection();
        $ticket = new DeskPROEntity\Ticket();
        $ticket
            ->setRef($this->getOrCreateTicketRef($importing_entity->getRef()))
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
            ->resetLabels();

        if ($importing_entity->getAgentEmail()) {
            $this->logInfo(sprintf('Applying ticket agent `%s`', $importing_entity->getAgentEmail()));
            $agent = $this->getPersonMapper()->findOneByEmail($importing_entity->getAgentEmail());
            if (!$agent->isAgent()) {
                throw new ImporterException(sprintf('Person `%s` is not an agent', $agent->getEmailAddress()));
            }
        }
        foreach ($importing_entity->getMessages() as $message) {
            $ticket->addMessage($this->createTicketMessage($message));
        }
        foreach ($importing_entity->getParticipants() as $participant) {
            $ticket->addParticipant($this->createParticipant($participant));
        }
        foreach ($importing_entity->getLabels() as $label) {
            $ticket->addLabel($this->createTicketLabel($label));
        }
        foreach ($importing_entity->getCustomFields() as $custom_field) {
            // todo implement
            $ticket->setCustomData(null, null, null);
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
    private function createTicketMessage(Entity\TicketMessage $importing_entity)
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
            $message->addAttachment($this->createAttachment(
                $importing_attachment,
                $importing_entity->getPersonEmail()
            ));
        }

        return $message;
    }

    /**
     * Returns the importing DeskPro doctrine ticket message attachment entity
     *
     * @param Entity\Attachment $importing_entity
     * @param string                  $message_person_email
     *
     * @return DeskPROEntity\TicketAttachment
     */
    private function createAttachment(Entity\Attachment $importing_entity, $message_person_email)
    {
        $email = $importing_entity->getPersonEmail() ? : $message_person_email;
        $attachment = new DeskPROEntity\TicketAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($email))
            ->setBlob($this->blob_adapter->createByAttachment($importing_entity));

        return $attachment;
    }

    /**
     * Returns the importing DeskPro doctrine ticket participant entity
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

        return $participant;
    }

    /**
     * Returns the importing DeskPro doctrine ticket label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelTicket
     */
    private function createTicketLabel($label)
    {
        $ticket_label = new DeskPROEntity\LabelTicket();
        $ticket_label->setLabel($label);

        return $ticket_label;
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
                $this->logWarning(sprintf('New ticket category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Validates if current ref is already exist
     *
     * todo Is it ref to original source? Can it be not unique?
     * todo Should we check if random is unique?
     *
     * @param $ref
     * @return string
     */
    private function getOrCreateTicketRef($ref)
    {
        $existing_ticket = $this->getTicketMapper()->findOneByRef($ref, false);
        return $existing_ticket ? Strings::random(10, Strings::CHARS_ALPHANUM_IU) : $ref;
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
