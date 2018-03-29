<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset as ThemeSetAssetEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ThemeSetAsset as ThemeSetAssetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ThemeSetAssetHandler.
 */
class ThemeSetAssetHandler extends AbstractEntityHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * ThemeSetAssetHandler constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ThemeSetAssetEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ThemeSetAssetEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $url        = null;
        $blobAuthId = null;

        // Generate a URL for user custom assets uploaded in the Portal Designer
        if (in_array(AssetsManager::CUSTOM_ASSET_TAG, $entity->getTags())
            || in_array(AssetsManager::CUSTOM_LOGO_TAG, $entity->getTags())
            || in_array(AssetsManager::CUSTOM_FAVICON_TAG, $entity->getTags())
        ) {
            $url = $entity->getBlob()->getDownloadUrl(true, true);
        } elseif (in_array('inline-image', $entity->getTags())
            || in_array('attachment', $entity->getTags())
        ) {
            $url        = $entity->getBlob()->getDownloadUrl(true, true);
            $blobAuthId = $entity->getBlob()->getAuthId();
        }

        return new ThemeSetAssetModel($entity, $url, $blobAuthId);
    }
}
