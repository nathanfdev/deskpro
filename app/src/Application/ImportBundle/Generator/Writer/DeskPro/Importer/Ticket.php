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
        /** @var Mapper\TicketCategory $ticket_category_mapper */
        $ticket_category_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET_CATEGORY);

        $this->records = new ArrayCollection();

        $ticket = new DeskPROEntity\Ticket();
        $ticket
            ->setSubject($importing_entity->getSubject())
            ->setPerson($person_mapper->findOneByEmail($importing_entity->getPersonEmail()))
            ->setLanguageId($this->getLanguageId($importing_entity->getLanguage()))
            ->setDepartment($this->findOrCreateDepartment($importing_entity->getDepartment()))
            ->setPriority($this->findOrCreateTicketPriority($importing_entity->getPriority()))
        ;

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
     *
     * @param string $title
     * @return DeskPROEntity\TicketPriority|mixed|null
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

                $this->logWarning(sprintf('New ticket priority creating `%s`', $priority->getTitle()));
                $this->records->add($priority);
            }
        }

        return $priority;
    }
}
