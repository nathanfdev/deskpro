<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition as FieldDefinition;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Util\ClassUtils;

class CustomFieldData extends AbstractEntityRepository
{
    /**
     * @param  DomainObject $owner
     * @param  DomainObject $context
     * @param  Layout       $layout
     * @return mixed
     */
    public function getAllDataForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
    {
        $qb = $this->createQueryBuilder('da')
            ->select('da, de', 'rde')
            ->innerJoin('da.definition', 'de')
            ->innerJoin('da.root_definition', 'rde')
            ->where('da.owner_id = :owner_id and de.owner_class = :owner_class and rde.is_enabled = 1')
            ->setParameters(array(
                'owner_id' => (int) $owner['id'], // null -> 0
                'owner_class' => ClassUtils::getClass($owner),
            ));

        if ($layout) {
            if (!$in = $layout->getIdsOfFieldType('custom_field')) {
                return array();
            }
            $qb
                ->andWhere('rde.id in (:fields)')
                ->setParameter('fields', $in, Connection::PARAM_INT_ARRAY);
        }

        if ($context) {
            $qb
                ->andWhere('rde.context_class is null or (de.context_class = :context_class and de.context_id = :cid)')
                ->setParameter('context_class', ClassUtils::getClass($context))
                ->setParameter('cid', $context['id']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  FieldDefinition $definition
     * @param  DomainObject    $owner
     * @return array|null
     */
    public function getFieldData(FieldDefinition $definition, DomainObject $owner)
    {
        if (!$definition['id'] || !$owner['id']) {
            return null;
        }

        $qb = $this->createQueryBuilder('da')
            ->join('da.definition', 'de')
            ->where('da.owner_id = :oid and da.root_definition = :did')
            ->setParameter('oid', $owner['id'])
            ->setParameter('did', $definition['id'])
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  FieldDefinition $definition
     * @param  DomainObject    $owner
     * @return mixed|null
     */
    public function getFieldRawData(FieldDefinition $definition, DomainObject $owner)
    {
        if (!$definition['id'] || !$owner['id']) {
            return null;
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('da.value, da.input, de.title')
            ->from($this->getEntityName(), 'da')
            ->join('da.definition', 'de')
            ->where('da.owner_id = :oid and da.root_definition = :did')
            ->setParameter('oid', $owner['id'])
            ->setParameter('did', $definition['id'])
        ;

        return $qb->getQuery()->execute();
    }
}
