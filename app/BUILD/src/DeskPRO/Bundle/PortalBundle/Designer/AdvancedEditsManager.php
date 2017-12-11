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

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
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
     * @var ThemeSet
     */
    private $themeSet;

    /**
     * @var ThemeSet
     */
    private $editThemeSet;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $mainScssPath;

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
     * @param ThemeSet           $themeSet
     * @param ThemeSet           $editThemeSet
     * @param \Twig_Environment  $twig
     * @param string             $mainScssPath
     */
    public function __construct(
        EntityManager $entityManager,
        DeskproBlobStorage $blobStorage,
        ThemeSet $themeSet,
        ThemeSet $editThemeSet,
        \Twig_Environment $twig,
        $mainScssPath
    ) {
        $this->entityManager = $entityManager;
        $this->blobStorage   = $blobStorage;
        $this->themeSet      = $themeSet;
        $this->editThemeSet  = $editThemeSet;
        $this->twig          = $twig;
        $this->mainScssPath  = $mainScssPath;
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
            $this->saveThemeSetAsset(self::MAIN_SCSS_ASSET_NAME, self::MAIN_SCSS_ASSET_TAG, 'text/css', file_get_contents($this->mainScssPath));
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
        $blob      = $this->findBlob(self::MAIN_SCSS_ASSET_NAME, $this->editThemeSet);
        $failedStr = '';

        if ($blob) {
            try {
                return $this->blobStorage->copyBlobRecordToString($blob);
            } catch (\Exception $e) {
                $failedStr = sprintf('/* Failed to load custom CSS from blob %s */', $blob->getId())."\n\n";
            }
        }

        return $failedStr.file_get_contents($this->mainScssPath);
    }

    /**
     * @return string
     */
    public function getEditThemeSetScss()
    {
        $blob = $this->findBlob(self::CUSTOM_SCSS_ASSET_NAME, $this->editThemeSet);

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
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->editThemeSet);

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
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->themeSet);

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
        $themeSet = $this->editThemeSet;

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
}
