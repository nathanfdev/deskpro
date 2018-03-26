<?php

namespace DeskPRO\Bundle\AppBundle\Twig;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use DeskPRO\Bundle\PortalBundle\Helper\PortalModeTrait;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\Extension\AssetsExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PortalCustomizationsTwigExtension.
 */
class PortalCustomizationsTwigExtension extends \Twig_Extension
{
    use PortalModeTrait;

    private static $default_ltr_css_asset = 'DeskPRO_PortalBundle_style.css';
    private static $default_rtl_css_asset = 'DeskPRO_PortalBundle_rtl_style.css';

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
            new \Twig_SimpleFunction('portal_css_url', [$this, 'getPortalCssUrl']),
            new \Twig_SimpleFunction('portal_custom_js', [$this, 'getPortalCustomJs']),
            new \Twig_SimpleFunction('portal_custom_logo', [$this, 'getPortalCustomLogo']),
            new \Twig_SimpleFunction('portal_custom_favicon', [$this, 'getPortalCustomFavicon']),
        ];
    }

    /**
     * Get URL of the portal CSS file.
     *
     * Depending on custom styles availability returns link to the custom .css or link to the default file
     *
     * @param string $textDirection LTR or RTL, or null to use the current language
     *
     * @return string
     */
    public function getPortalCssUrl($textDirection = null)
    {
        if ($textDirection === null) {
            /** @var Language $lang */
            if (!$lang = $this->container->get('language_stack')->getActive()) {
                $lang = $this->container->get('language_stack')->getDefaultLanguage();
            }
            $textDirection = $lang->getDirection();
        }

        $textDirection = strtoupper($textDirection);

        $blob = $this->isPreviewMode($this->container)
                ? $this->getStylesManager()->getEditThemeSetCssBlob($textDirection)
                : $this->getStylesManager()->getCssBlob($textDirection);

        if ($blob) {
            $parameters = [
                'filename'     => $blob->getFilenameSafe(),
                'blob_auth_id' => $blob->getAuthId(),
                'local'        => true,
            ];
            /** @var BlobRepository $blobRepository */
            $blobRepository = $this->container->getEm()->getRepository(Blob::class);
            if ($gzBlob = $blobRepository->getSystemBlob("blob-$blob[id]-gzip")) {
                $parameters['g'] = $gzBlob->getAuthCode();
            }

            return $this->getRouter()->generate(
                'serve_blob',
                $parameters,
                RouterInterface::ABSOLUTE_PATH
            );
        } else {
            if ($textDirection === 'RTL') {
                return $this->getAssetsExtension()->getAssetUrl(self::$default_rtl_css_asset, 'app_assets');
            } else {
                return $this->getAssetsExtension()->getAssetUrl(self::$default_ltr_css_asset, 'app_assets');
            }
        }
    }

    /**
     * @return string
     */
    public function getPortalCustomJs()
    {
        return $this->isPreviewMode($this->container)
                   ? $this->getAdvancedEditsManager()->getEditThemeSetJs()
                   : $this->getAdvancedEditsManager()->getJs();
    }

    /**
     * @return string|null
     */
    public function getPortalCustomLogo()
    {
        $asset = $this->isPreviewMode($this->container)
            ? $this->getAssetsManager()->getEditThemeSetBlobAsset()
            : $this->getAssetsManager()->getBlobAsset(AssetsManager::CUSTOM_LOGO_TAG);

        if ($asset) {
            return $asset->getBlob()->getDownloadUrl(true);
        }

        return;
    }

    public function getPortalCustomFavicon()
    {
        $asset = $this->isPreviewMode($this->container)
            ? $this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG)
            : $this->getAssetsManager()->getBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG);

        if ($asset) {
            return ['url' => $asset->getBlob()->getDownloadUrl(true), 'type' => $asset->getBlob()->getContentType()];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'portal_customizations';
    }

    /**
     * @return StylesManager
     */
    private function getStylesManager()
    {
        return $this->container->get('dp.portal.designer.styles_manager');
    }

    /**
     * @return AdvancedEditsManager
     */
    private function getAdvancedEditsManager()
    {
        return $this->container->get('dp.portal.designer.advanced_edits_manager');
    }

    /**
     * @return AssetsManager
     */
    private function getAssetsManager()
    {
        return $this->container->get('dp.portal.designer.assets_manager');
    }

    /**
     * @return AssetsExtension
     */
    private function getAssetsExtension()
    {
        return $this->container->get('twig')->getExtension('asset');
    }

    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->container->get('router');
    }
}
