<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Brand as BrandModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandHandler.
 */
class BrandHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return BrandEntity::class;
    }

    /**
     * BrandHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param BrandEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $entityRepository = $this->entityManager->getRepository(ThemeSetAsset::class);
        $themeSetAsset    = $entityRepository->findOneBy([
            'theme_set' => $entity->getThemeSet(),
            'tags'      => [AssetsManager::CUSTOM_LOGO_TAG], ]);

        return new BrandModel($entity, $themeSetAsset ? $themeSetAsset->getBlob()->getDownloadUrl(true, true) : null);
    }
}
