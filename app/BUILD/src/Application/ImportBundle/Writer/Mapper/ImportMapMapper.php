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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\ImportMap;
use Application\ImportBundle\Model\OidAwareModelInterface;
use Orb\Util\Strings;

/**
 * Import map record mapper.
 *
 * Class ImportMap
 */
class ImportMapMapper extends AbstractEntityManagerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ImportMap::class;
    }

    /**
     * @param OidAwareModelInterface $model
     *
     * @return ImportMap
     */
    public function findOneByModel(OidAwareModelInterface $model)
    {
        if (!$model->getOid()) {
            throw new \RuntimeException('Primary entity does not have OID. Unable to find existing entity ID by import map reference.');
        }

        $criteria = [
            'old_id'   => $model->getOid(),
            'typename' => self::getImportMapKey($model),
        ];

        return $this->findOneBy($criteria, false);
    }

    /**
     * @param $model
     *
     * @return string|null
     */
    public function findIdByModel(OidAwareModelInterface $model)
    {
        $mapEntity = $this->findOneByModel($model);

        return $mapEntity ? $mapEntity->getNewId() : null;
    }

    /**
     * Saves an entity mapping.
     *
     * @param OidAwareModelInterface $model
     * @param mixed                  $entity
     *
     * @return $this
     */
    public function saveMapping(OidAwareModelInterface $model, $entity)
    {
        if (!$model->getOid()) {
            throw new \RuntimeException('Primary entity does not have OID. Unable to store import map reference.');
        }

        $mapEntity = $this->findOneByModel($model) ?: new ImportMap();
        $mapEntity
            ->setTypename(self::getImportMapKey($model))
            ->setOldId($model->getOid())
            ->setNewId($entity->getId())
        ;

        $this->em->persist($mapEntity);
        $this->em->flush();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getImportMapKey($model)
    {
        $modelClass = (new \ReflectionClass($model))->getShortName();
        $modelClass = Strings::camelCaseToUnderscore($modelClass);

        return 'importer_'.$modelClass;
    }
}
