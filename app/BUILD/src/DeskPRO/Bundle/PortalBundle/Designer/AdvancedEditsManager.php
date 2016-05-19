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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
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
    const CUSTOM_SCSS_ASSET_NAME      = 'custom-styles.scss';
    const CUSTOM_SCSS_ASSET_TAG       = 'custom_style';
    const CUSTOM_JS_ASSET_NAME        = 'custom-javascript.js';
    const CUSTOM_JS_ASSET_TAG         = 'custom_js';

    /**
     * @var EntityManager
     */
    private $em;

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
     * @param EntityManager     $em
     * @param ThemeSet          $theme_set
     * @param ThemeSet          $edit_theme_set
     * @param \Twig_Environment $twig
     */
    public function __construct(EntityManager $em, ThemeSet $theme_set, ThemeSet $edit_theme_set, \Twig_Environment $twig)
    {
        $this->em             = $em;
        $this->theme_set      = $theme_set;
        $this->edit_theme_set = $edit_theme_set;
        $this->twig           = $twig;
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
        if (array_key_exists('scss', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_SCSS_ASSET_NAME, self::CUSTOM_SCSS_ASSET_TAG, $data['scss']);
        }
        if (array_key_exists('javascript', $data)) {
            $this->saveThemeSetAsset(self::CUSTOM_JS_ASSET_NAME, self::CUSTOM_JS_ASSET_TAG, $data['javascript']);
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        $data = [
            'header'     => $this->findOrCreateTemplate(self::CUSTOM_HEADER_TEMPLATE_NAME)->getTemplateCode(),
            'footer'     => $this->findOrCreateTemplate(self::CUSTOM_FOOTER_TEMPLATE_NAME)->getTemplateCode(),
            'scss'       => $this->getEditThemeSetScss(),
            'javascript' => $this->getEditThemeSetJs(),
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getEditThemeSetScss()
    {
        $scss = (string) $this->findOrCreateBlobStorage(self::CUSTOM_SCSS_ASSET_NAME, self::CUSTOM_SCSS_ASSET_TAG)
            ->getData();

        if (empty(trim($scss))) {
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
    }

    /**
     * @return string
     */
    public function getEditThemeSetJs()
    {
        $storage = $this->findBlobStorage(self::CUSTOM_JS_ASSET_NAME, $this->edit_theme_set);

        return $storage ? (string) $storage->getData() : '';
    }

    /**
     * @return string
     */
    public function getJs()
    {
        $storage = $this->findBlobStorage(self::CUSTOM_JS_ASSET_NAME, $this->theme_set);

        return $storage ? (string) $storage->getData() : '';
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
     * @param string $code
     */
    private function saveThemeSetAsset($name, $tag, $code)
    {
        $storage       = $this->findOrCreateBlobStorage($name, $tag, md5($code));
        $storage->data = $code;
        $this->em->persist($storage);
        $this->em->flush();
    }

    /**
     * @param string        $name
     * @param string        $tag
     * @param null|string   $blob_hash Is used to create a new Blob when can't find an existing
     * @param null|ThemeSet $theme_set Search/create within the theme set, current edit ThemeSet is used by default
     *
     * @return BlobStorage
     */
    private function findOrCreateBlobStorage($name, $tag, $blob_hash = '', ThemeSet $theme_set = null)
    {
        $theme_set or $theme_set = $this->edit_theme_set;

        // Find existing or create a new ThemeSetAsset

        $asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy(compact('name', 'theme_set'));
        if (!$asset) {
            $asset = new ThemeSetAsset();
            $asset->setName($name);
            $asset->setThemeSet($theme_set);
            $asset->setTags([$tag]);
            $this->em->persist($asset);
            $this->em->flush();
        }

        // Find existing or create a new Blob

        if (!$blob = $asset->getBlob()) {
            $blob               = new Blob();
            $blob->filename     = $name;
            $blob->content_type = 'text';
            $blob->blob_hash    = $blob_hash;
            $asset->setBlob($blob);
            $this->em->persist($blob);
            $this->em->persist($asset);
            $this->em->flush();
        }

        // Find existing or create a new BlobStorage

        $storage = $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        if (!$storage) {
            $storage          = new BlobStorage();
            $storage->blob_id = $blob->getId();
        }

        return $storage;
    }

    /**
     * @param string        $name
     * @param ThemeSet|null $theme_set
     *
     * @return BlobStorage|null
     */
    private function findBlobStorage($name, ThemeSet $theme_set = null)
    {
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy(compact('name', 'theme_set'))) {
            if ($blob = $asset->getBlob()) {
                return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
            }
        }

        return;
    }
}
