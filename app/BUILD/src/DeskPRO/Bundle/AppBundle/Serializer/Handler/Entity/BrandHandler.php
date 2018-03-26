<?php

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
