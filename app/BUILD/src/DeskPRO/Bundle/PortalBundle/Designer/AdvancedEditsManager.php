<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

/**
 * Class AdvancedEditsManager.
 */
class AdvancedEditsManager
{
    const MAIN_SCSS_ASSET_NAME   = 'main.scss';
    const MAIN_SCSS_ASSET_TAG    = 'main';
    const CUSTOM_SCSS_ASSET_NAME = 'custom-styles.scss';
    const CUSTOM_SCSS_ASSET_TAG  = 'custom_style';
    const CUSTOM_JS_ASSET_NAME   = 'custom-javascript.js';
    const CUSTOM_JS_ASSET_TAG    = 'custom_js';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $assetDir;

    /**
     * Array of name => true for assets that were updated.
     *
     * @var array
     */
    private $changedAssets = [];

    /**
     * Constructor.
     *
     * @param EntityManager      $entityManager
     * @param DeskproBlobStorage $blobStorage
     * @param BrandStack         $brandStack
     * @param \Twig_Environment  $twig
     * @param string             $assetDir
     */
    public function __construct(
        EntityManager      $entityManager,
        DeskproBlobStorage $blobStorage,
        BrandStack         $brandStack,
        \Twig_Environment  $twig,
        $assetDir
    ) {
        $this->entityManager = $entityManager;
        $this->blobStorage   = $blobStorage;
        $this->brandStack    = $brandStack;
        $this->twig          = $twig;
        $this->assetDir      = $assetDir;
    }

    /**
     * @param array $data
     */
    public function save(array $data)
    {
        if (array_key_exists('custom_scss', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_SCSS_ASSET_NAME, self::CUSTOM_SCSS_ASSET_TAG, 'text/css', $data['custom_scss']);
        }
        if (array_key_exists('main_scss', $data)) {
            $this->saveThemeSetAsset(self::MAIN_SCSS_ASSET_NAME, self::MAIN_SCSS_ASSET_TAG, 'text/css', $data['main_scss']);
        }
        if (array_key_exists('javascript', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_JS_ASSET_NAME, self::CUSTOM_JS_ASSET_TAG, 'text/javascript', $data['javascript']);
        }
    }

    /**
     * @param array $data
     */
    public function delete(array $data)
    {
        if (array_key_exists('custom_scss', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_SCSS_ASSET_NAME, self::CUSTOM_SCSS_ASSET_TAG, 'text/css', $this->getCustomScssCode());
        }
        if (array_key_exists('main_scss', $data)) {
            $this->saveThemeSetAsset(self::MAIN_SCSS_ASSET_NAME, self::MAIN_SCSS_ASSET_TAG, 'text/css', file_get_contents($this->getMainScssPath()));
        }
        if (array_key_exists('javascript', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_JS_ASSET_NAME, self::CUSTOM_JS_ASSET_TAG, 'text/javascript', '');
        }
    }

    /**
     * @return bool
     */
    public function hasChangedCssFiles()
    {
        return isset($this->changedAssets[self::CUSTOM_SCSS_ASSET_NAME])
            || isset($this->changedAssets[self::MAIN_SCSS_ASSET_NAME]);
    }

    /**
     * @return array
     */
    public function get()
    {
        $data = [
            'main_scss'   => $this->getMainScss(),
            'custom_scss' => $this->getEditThemeSetScss(),
            'javascript'  => $this->getEditThemeSetJs(),
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getMainScss()
    {
        if ($this->getEditThemeSet()->getThemeId() === 'helpcenter') {
            return file_get_contents($this->getMainScssPath());
        } else {
            $blob      = $this->findBlob(self::MAIN_SCSS_ASSET_NAME, $this->getEditThemeSet());
            $failedStr = '';

            if ($blob) {
                try {
                    return $this->blobStorage->copyBlobRecordToString($blob);
                } catch (\Exception $e) {
                    $failedStr = sprintf('/* Failed to load custom CSS from blob %s */', $blob->getId())."\n\n";
                }
            }

            return $failedStr.file_get_contents($this->getMainScssPath());
        }
    }

    /**
     * @return string
     */
    public function getEditThemeSetScss()
    {
        $blob = $this->findBlob(self::CUSTOM_SCSS_ASSET_NAME, $this->getEditThemeSet());

        if ($blob) {
            try {
                return $this->blobStorage->copyBlobRecordToString($blob);
            } catch (\Exception $e) {
                return sprintf('/* Failed to load custom CSS from blob %s */', $blob->getId());
            }
        }

        return $this->getCustomScssCode();
    }

    protected function getCustomScssCode()
    {
        $scss = <<<'CODE'
/*
    Use this template to add custom CSS to your site.
    
    The code you enter here will be evaluated as SCSS which is an extension of CSS
    that adds nesting features, variables, mixins, inheritance and more.
    
    Read more about SCSS here: http://sass-lang.com/guide
*/

CODE;

        return $scss;
    }

    /**
     * @return string
     */
    public function getEditThemeSetJs()
    {
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->getEditThemeSet());

        try {
            return $blob ? $this->blobStorage->copyBlobRecordToString($blob) : '';
        } catch (\Exception $e) {
            return '// Failed to load custom JS';
        }
    }

    /**
     * @return string
     */
    public function getJs()
    {
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->getThemeSet());

        try {
            return $blob ? $this->blobStorage->copyBlobRecordToString($blob) : '';
        } catch (\Exception $e) {
            return '// Failed to load custom JS';
        }
    }

    /**
     * @param string $name
     * @param string $tag
     * @param string $mimeType
     * @param string $code
     *
     * @return Blob
     */
    private function saveThemeSetAsset($name, $tag, $mimeType, $code)
    {
        $themeSet = $this->getEditThemeSet();

        $oldBlob = null;

        // Find existing or create a new ThemeSetAsset
        $asset = $this->entityManager->getRepository(ThemeSetAsset::class)->findOneBy([
            'name'      => $name,
            'theme_set' => $themeSet,
        ]);

        if ($asset) {
            $oldBlob = $asset->getBlob();
        } else {
            $asset = new ThemeSetAsset();
        }

        if ($oldBlob) {
            try {
                $oldContent = $this->blobStorage->copyBlobRecordToString($oldBlob);
                if ($oldContent === $code) {
                    // no change
                    return $oldBlob;
                }
            } catch (\Exception $e) {
            }
        }

        // record it as changed
        $this->changedAssets[$name] = true;

        $blob = $this->blobStorage->createBlobRecordFromString($code, $name, $mimeType, ['tag' => 'brand_asset.'.$tag]);

        $asset->setName($name);
        $asset->setThemeSet($themeSet);
        $asset->setTags([$tag]);
        $asset->setBlob($blob);
        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        if ($oldBlob) {
            $this->blobStorage->deleteBlobRecord($oldBlob);
        }

        return $blob;
    }

    /**
     * @param string        $name
     * @param ThemeSet|null $themeSet
     *
     * @return Blob|null
     */
    private function findBlob($name, ThemeSet $themeSet = null)
    {
        /** @var ThemeSetAsset $asset */
        $asset = $this->entityManager->getRepository(ThemeSetAsset::class)->findOneBy([
            'name'      => $name,
            'theme_set' => $themeSet,
        ]);

        return $asset ? $asset->getBlob() : null;
    }

    /**
     * @return ThemeSet
     */
    private function getThemeSet()
    {
        return $this->brandStack->getActive()->getBrand()->getThemeSet();
    }

    /**
     * @return ThemeSet
     */
    private function getEditThemeSet()
    {
        return $this->brandStack->getActive()->getBrand()->getEditThemeSet();
    }

    /**
     * @return string
     */
    private function getMainScssPath()
    {
        if ($this->getEditThemeSet()->getThemeId() === 'helpcenter') {
            return $this->assetDir.'/pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/helpcenter_main.scss';
        } elseif ($this->getEditThemeSet()->getThemeId() === 'edit_theme_set_id') {
            // a workaround for tests
            return $this->assetDir;
        } else {
            return $this->assetDir.'/pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/main.scss';
        }
    }
}
