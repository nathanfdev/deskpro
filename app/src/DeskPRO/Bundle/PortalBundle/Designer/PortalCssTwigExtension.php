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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\Extension\AssetsExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PortalCssTwigExtension.
 */
class PortalCssTwigExtension extends \Twig_Extension
{
    private static $default_css_asset = 'DeskPRO_PortalBundle_style.css';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('portal_css_url', array($this, 'getPortalCssUrl')),
        ];
    }

    /**
     * Get URL of the portal CSS file.
     *
     * Depending on custom styles availability returns link to the custom .css or link to the default file
     */
    public function getPortalCssUrl()
    {
        $blob_storage = $this->isPreviewMode()
                      ? $this->getStylesManager()->getEditThemeSetCssBlobStorage()
                      : $this->getStylesManager()->getCssBlobStorage();

        if ($blob_storage) {
            return $this->getRouter()->generate(
                'dp_portal_designer_custom_css',
                ['version' => $blob_storage->getId(), 'preview' => intval($this->isPreviewMode())],
                true
            );
        } else {
            return $this->getAssetsExtension()->getAssetUrl(self::$default_css_asset, 'app_assets');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'portal_css';
    }

    /**
     * @return StylesManager
     */
    private function getStylesManager()
    {
        return $this->container->get('dp.portal.designer.styles_manager');
    }

    /**
     * @return AssetsExtension
     */
    private function getAssetsExtension()
    {
        return $this->container->get('templating.email.twig.extension.assets');
    }

    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * @return bool
     */
    private function isPreviewMode()
    {
        /** @var PortalModeStorage $portal_mode_storage */
        $portal_mode_storage = $this->container->get('portal_mode_storage');
        if ($mode = $portal_mode_storage->getMode()) {
            return $mode->isAdminPreview();
        }

        return false;
    }
}
