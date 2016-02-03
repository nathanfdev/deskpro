<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * deskpro.
 *
 * @author Denis Ranneft (aka Immortal) <denis@ranneft.ru>
 * Date: 11.09.15
 * Time: 20:58
 */
namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ThemeSetAssetTransformer.
 */
class ThemeSetAssetTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'name',
            'theme_set',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var ThemeSetAsset $entity */
        $entity = $transformation_request->getDataToBeTransformed();

        $data = [];

        // Generate a URL for user custom assets uploaded in the Portal Designer
        if (in_array(AssetsManager::CUSTOM_ASSET_TAG, $entity->getTags())
            || in_array(AssetsManager::CUSTOM_LOGO_TAG, $entity->getTags())
        ) {
            $data['url'] = $this->router->generate(
                'dp_portal_custom_asset', ['name' => $entity->getName()], RouterInterface::ABSOLUTE_URL);
        }

        return $data;
    }
}
