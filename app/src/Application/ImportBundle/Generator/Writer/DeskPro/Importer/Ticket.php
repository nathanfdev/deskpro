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

/**
 * DeskPro ticket importer
 *
 * Class Ticket
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Ticket extends AbstractImporter
{
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

        /** @var Mapper\Person $person_mapper */
        $person_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON);
        /** @var Mapper\Ticket $ticket_mapper */
        $ticket_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET);

        $this->records = new ArrayCollection();

        $ticket = new DeskPROEntity\Ticket();
        $ticket
            ->setSubject($importing_entity->getSubject())
            ->setPerson($person_mapper->findOneByEmail($importing_entity->getPersonEmail()))
            ->setOrganization($this->findOrCreateOrganization($importing_entity->getOrganization()))
            ->setLanguageId($this->findLanguageId($importing_entity->getLanguage()))
            ->setDepartment($this->findOrCreateDepartment($importing_entity->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($importing_entity->getPriority()))
            ->setCategory($this->findOrCreateTicketCategory($importing_entity->getCategory()))
            ->setStatus($importing_entity->getStatus())
        ;

//        $record['date_created']  = $tval->date_created ? $tval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
//        $record['date_archived'] = $tval->date_archived ? $tval->date_archived->format('Y-m-d H:i:s') : null;
//        $record['date_resolved'] = $tval->date_resolved ? $tval->date_resolved->format('Y-m-d H:i:s') : null;

        if ($importing_entity->getAgentEmail()) {
            $agent = $person_mapper->findOneByEmail($importing_entity->getAgentEmail());
            if (!$agent->isAgent()) {
                throw new ImporterException(sprintf('Person `%s` is not agent', $agent->getEmailAddress()));
            }
        }

        $this->records->add($ticket);
        return $this->records;
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
        /** @var Mapper\TicketPriority $mapper */
        $mapper   = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_PRIORITY);
        $priority = null;

        if ($title) {
            $priority = $mapper->findOneByTitle($title, false);
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
        /** @var Mapper\TicketCategory $mapper */
        $mapper   = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_CATEGORY);
        $category = null;

        if ($title) {
            $category = $mapper->findOneByTitle($title, false);
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
}
