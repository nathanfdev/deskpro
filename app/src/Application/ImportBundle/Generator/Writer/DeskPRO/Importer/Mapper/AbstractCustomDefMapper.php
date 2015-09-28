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
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Entity;

/**
 * Class AbstractCustomDefMapper
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper
 */
abstract class AbstractCustomDefMapper implements MapperInterface
{
    /**
     * @var EntityRepository\CustomDefAbstract
     */
    protected $custom_def_repository;

    /**
     * @var EntityRepository\ImportMap
     */
    protected $import_map_repository;

    /**
     * Constructor
     *
     * @param EntityRepository\CustomDefAbstract $custom_def_repository
     * @param EntityRepository\ImportMap         $import_map_repository
     */
    public function __construct(EntityRepository\CustomDefAbstract $custom_def_repository, EntityRepository\ImportMap $import_map_repository)
    {
        $this->custom_def_repository = $custom_def_repository;
        $this->import_map_repository = $import_map_repository;
    }

    /**
     * Find a choice custom def entity
     * We store value for choice custom fields like "A > A1"
     *
     * MyField
     *   Option A
     *     |- Option A1
     *     |- Option A2
     *   Option B
     *     |- Option B1
     *     |- Option B2
     *
     * @param array|string                    $choice_chain
     * @param DeskPROEntity\CustomDefAbstract $parent
     *
     * @return DeskPROEntity\CustomDefAbstract
     */
    public function findChoiceCustomDef($choice_chain, DeskPROEntity\CustomDefAbstract $parent)
    {
        if (is_string($choice_chain)) {
            $choice_chain = explode('>', $choice_chain);
            $choice_chain = array_map('trim', $choice_chain);
        }

        $custom_field_def = $this->findOneBy(array(
            'title'  => array_shift($choice_chain),
            'parent' => $parent,
        ));

        return empty($choice_chain) ? $custom_field_def : $this->findChoiceCustomDef($choice_chain, $custom_field_def);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $id = $this->findImportMapNewId($criteria);
        if ($id) {
            $record = $this->custom_def_repository->find($id);
        } else {
            $record = $this->custom_def_repository->findOneBy($criteria);
        }

        /** @var DeskPROEntity\CustomDefAbstract $record */
        if ( ! $record && $throw_exception) {
            throw new MapperException(sprintf('Custom def `%s` not found', $this->getType()), $criteria);
        }

        return $record;
    }

    /**
     * Returns new id by import map
     *
     * @param array $criteria
     *
     * @return null|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function findImportMapNewId(array &$criteria)
    {
        $record = null;
        if (isset($criteria['entity'])) {
            $entity = $criteria['entity'];
            unset($criteria['entity']);

            if ( ! $entity instanceof Entity\EntityInterface) {
                throw new \RuntimeException('Criteria `entity` should be instance of Entity\EntityInterface');
            }

            if ($entity->getImportMapKey()) {
                $qb = $this->import_map_repository->createQueryBuilder('i');
                $qb
                    ->select('i')
                    ->andWhere($qb->expr()->eq('i.typename', '?0'))
                    ->andWhere($qb->expr()->eq('i.old_id', '?1'))
                    ->setParameters(array(
                        $entity->getImportMapKey(),
                        $entity->getOid()
                    ))
                ;

                /** @var DeskPROEntity\ImportMap $import_map */
                $import_map = $qb->getQuery()->getOneOrNullResult();
                if ($import_map) {
                    return $import_map->getNewId();
                }
            }
        }

        return null;
    }
}
