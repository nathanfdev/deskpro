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
    const CUSTOM_HEADER_TEMPLATE_NAME = 'Theme:Internal:custom-header.html.twig';
    const CUSTOM_FOOTER_TEMPLATE_NAME = 'Theme:Internal:custom-footer.html.twig';
    const MAIN_SCSS_ASSET_NAME        = 'main.scss';
    const MAIN_SCSS_ASSET_TAG         = 'main';
    const CUSTOM_SCSS_ASSET_NAME      = 'custom-styles.scss';
    const CUSTOM_SCSS_ASSET_TAG       = 'custom_style';
    const CUSTOM_JS_ASSET_NAME        = 'custom-javascript.js';
    const CUSTOM_JS_ASSET_TAG         = 'custom_js';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * @var ThemeSet
     */
    private $theme_set;

    /**
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $mainScssPath;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $bs
     * @param ThemeSet           $themeSet
     * @param ThemeSet           $editThemeSet
     * @param \Twig_Environment  $twig
     * @param string             $mainScssPath
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $bs, ThemeSet $themeSet, ThemeSet $editThemeSet, \Twig_Environment $twig, $mainScssPath)
    {
        $this->em             = $em;
        $this->bs             = $bs;
        $this->theme_set      = $themeSet;
        $this->edit_theme_set = $editThemeSet;
        $this->twig           = $twig;
        $this->mainScssPath   = $mainScssPath;
    }

    /**
     * @param array $data
     */
    public function save(array $data)
    {
        if (array_key_exists('header', $data)) {
            $this->saveTemplate(self::CUSTOM_HEADER_TEMPLATE_NAME, $data['header']);
        }
        if (array_key_exists('footer', $data)) {
            $this->saveTemplate(self::CUSTOM_FOOTER_TEMPLATE_NAME, $data['footer']);
        }
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
     * @return array
     */
    public function get()
    {
        $data = [
            'header'      => $this->findOrCreateTemplate(self::CUSTOM_HEADER_TEMPLATE_NAME)->getTemplateCode(),
            'footer'      => $this->findOrCreateTemplate(self::CUSTOM_FOOTER_TEMPLATE_NAME)->getTemplateCode(),
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
        $blob = $this->findBlob(self::MAIN_SCSS_ASSET_NAME, $this->edit_theme_set);

        if ($blob) {
            return $this->bs->copyBlobRecordToString($blob);
        }

        return file_get_contents($this->mainScssPath);
    }

    /**
     * @return string
     */
    public function getEditThemeSetScss()
    {
        $blob = $this->findBlob(self::CUSTOM_SCSS_ASSET_NAME, $this->edit_theme_set);

        if ($blob) {
            return $this->bs->copyBlobRecordToString($blob);
        }

        $scss = <<<CODE
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
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->edit_theme_set);

        return $blob ? $this->bs->copyBlobRecordToString($blob) : '';
    }

    /**
     * @return string
     */
    public function getJs()
    {
        $blob = $this->findBlob(self::CUSTOM_JS_ASSET_NAME, $this->theme_set);

        return $blob ? $this->bs->copyBlobRecordToString($blob) : '';
    }

    /**
     * Saves template assigning it to the edit ThemeSet.
     *
     * @param string $name
     * @param string $code
     */
    private function saveTemplate($name, $code)
    {
        $template = $this->findOrCreateTemplate($name);
        $template->setTemplate($code, $this->twig->compileSource($code, $name));
        $this->em->persist($template);
        $this->em->flush();
    }

    /**
     * @param string $name
     *
     * @return Template
     */
    private function findOrCreateTemplate($name)
    {
        $criteria = ['name' => $name, 'theme_set' => $this->edit_theme_set];
        $template = $this->em->getRepository(Template::class)->findOneBy($criteria);
        if (!$template) {
            $template            = new Template();
            $template->name      = $name;
            $template->theme_set = $this->edit_theme_set;
        }

        return $template;
    }

    /**
     * @param string $name
     * @param string $tag
     * @param string $mimeType
     * @param string $code
     *
     * @return ThemeSetAsset
     */
    private function saveThemeSetAsset($name, $tag, $mimeType, $code)
    {
        $theme_set = $this->edit_theme_set;

        $blob    = $this->bs->createBlobRecordFromString($code, $name, $mimeType, ['tag' => 'brand_asset.'.$tag]);
        $oldBlob = null;

        // Find existing or create a new ThemeSetAsset
        $asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy(compact('name', 'theme_set'));

        if ($asset) {
            $oldBlob = $asset->getBlob();
        } else {
            $asset = new ThemeSetAsset();
        }

        $asset->setName($name);
        $asset->setThemeSet($theme_set);
        $asset->setTags([$tag]);
        $asset->setBlob($blob);
        $this->em->persist($asset);
        $this->em->flush();

        if ($oldBlob) {
            $this->bs->deleteBlobRecord($oldBlob);
        }

        return $blob;
    }

    /**
     * @param string        $name
     * @param ThemeSet|null $theme_set
     *
     * @return Blob|null
     */
    private function findBlob($name, ThemeSet $theme_set = null)
    {
        /** @var ThemeSetAsset $asset */
        $asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy(compact('name', 'theme_set'));

        return $asset ? $asset->getBlob() : null;
    }
}
