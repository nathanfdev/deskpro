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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;

/**
 * Class PortalStylesCompiler.
 *
 * Re-compiles portal styles into the current brand's edit theme set
 */
class PortalStylesCompiler
{
    /**
     * @var string ThemeSet option name
     */
    public static $custom_vars_theme_set_option = 'custom_vars';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * @var string path to the portal SCSS file
     */
    private $styles_file_path;

    /**
     * @var string
     */
    private $custom_scss;

    /**
     * @param EntityManager $em
     * @param ThemeSet      $edit_theme_set
     * @param string        $styles_file_path
     * @param string        $custom_scss
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManager $em,
        ThemeSet $edit_theme_set,
        $styles_file_path,
        $custom_scss
    ) {
        $this->em = $em;
        if (!$this->styles_file_path = realpath($styles_file_path)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->styles_file_path}");
        }
        $this->custom_scss    = $custom_scss;
        $this->edit_theme_set = $edit_theme_set;
    }

    /**
     * Recompile portal css.
     *
     * @param array $variables
     */
    public function recompile(array $variables)
    {
        $themeSet = $this->edit_theme_set;
        $themeSet->setOption(self::$custom_vars_theme_set_option, $variables);

        $this->em->persist($themeSet);
        $this->em->flush();

        $css = $this->compileCss($variables);

        // Find existing or create a new blob storage for the custom Css
        if (!$blob_storage = $this->getEditThemeSetCssBlobStorage()) {
            $blob = new Blob();
            $blob->setFilename('portal.css');
            $blob->blob_hash = md5($css);
            $this->em->persist($blob);
            $this->em->flush();

            $asset = new ThemeSetAsset();
            $asset->setName('portal.css');
            $asset->setThemeSet($themeSet);
            $asset->setTags(['portal_css']);
            $asset->setBlob($blob);
            $this->em->persist($asset);
            $this->em->flush();
        } else {
            // Saving in a new BlobStorage instance to use its' $id as CSS version to bypass caches
            $blob = $this->getEditThemeSetCssBlob();
            $this->em->remove($blob_storage);
            $this->em->flush();
        }

        $blob_storage          = new BlobStorage();
        $blob_storage->blob_id = $blob->getId();
        $blob_storage->data    = $css;
        $this->em->persist($blob_storage);
        $this->em->flush();
    }

    /**
     * @return BlobStorage|null
     */
    public function getEditThemeSetCssBlobStorage()
    {
        if ($blob = $this->getEditThemeSetCssBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }

        return;
    }

    /**
     * Compile portal css from scss sources.
     *
     * @param array $variables
     *
     * @return string
     */
    private function compileCss(array $variables)
    {
        $compiler = new StylesheetCompiler();

        return $compiler->compile($this->styles_file_path, $variables, $this->custom_scss);
    }

    /**
     * @return Blob|null
     */
    private function getEditThemeSetCssBlob()
    {
        $criteria = [
            'theme_set' => $this->edit_theme_set,
            'name'      => 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }
}
