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
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $url    = null;
        $blobId = null;

        // Generate a URL for user custom assets uploaded in the Portal Designer
        if (in_array(AssetsManager::CUSTOM_ASSET_TAG, $entity->getTags())
            || in_array(AssetsManager::CUSTOM_LOGO_TAG, $entity->getTags())
            || in_array(AssetsManager::CUSTOM_FAVICON_TAG, $entity->getTags())
        ) {
            $url = $entity->getBlob()->getDownloadUrl(true, true);
        } elseif (in_array('inline-image', $entity->getTags())
            || in_array('attachment', $entity->getTags())
        ) {
            $url    = $entity->getBlob()->getDownloadUrl(true, true);
            $blobId = $entity->getBlob()->getId();
        }

        return new ThemeSetAssetModel($entity, $url, $blobId);
    }
}
