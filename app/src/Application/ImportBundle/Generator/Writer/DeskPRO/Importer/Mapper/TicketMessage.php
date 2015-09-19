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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper;

use Application\DeskPRO\Entity as DeskPROEntity;
use Doctrine\ORM\EntityManager;
use Application\ImportBundle\Entity;

/**
 * Class TicketMessage
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper
 */
final class TicketMessage implements MapperInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_MESSAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $record = null;
        if (isset($criteria['message'])) {
            $entity = $criteria['message'];
            if ( ! $entity instanceof Entity\TicketMessage) {
                throw new \RuntimeException('Criteria `message` should be instance of Entity\TicketMessage');
            }

            if ($entity->getImportMapKey()) {
                $qb = $this->em->createQueryBuilder();
                $qb
                    ->select('i')
                    ->from('DeskPRO:ImportMap', 'i')
                    ->andWhere($qb->expr()->eq('i.typename', '?0'))
                    ->andWhere($qb->expr()->eq('i.old_id', '?1'))
                    ->setParameters(array(
                        $entity->getImportMapKey(),
                        $entity->getOid()
                    ))
                ;

                /** @var DeskPROEntity\ImportMap $import_map */
                $import_map = $qb->getQuery()->getSingleResult();
                if ($import_map) {
                    /** @var DeskPROEntity\TicketMessage $record */
                    $record = $this->em->getRepository('DeskPRO:TicketMessage')->find($import_map->getNewId());
                }
            }
        }

        if ( ! $record && $throw_exception) {
            throw new MapperException('Ticket message not found', $criteria);
        }

        return $record;
    }
}
