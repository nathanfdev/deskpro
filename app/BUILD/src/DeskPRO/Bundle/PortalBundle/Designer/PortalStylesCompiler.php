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
    public static $customVarsThemeSetOption = 'custom_vars';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSet
     */
    private $editThemeSet;

    /**
     * @var string path to the portal SCSS file
     */
    private $stylesLrtFilePath;

    /**
     * @var string path to the portal SCSS file
     */
    private $stylesRtlFilePath;

    /**
     * @var string
     */
    private $customScss;

    /**
     * @param EntityManager $em
     * @param ThemeSet      $editThemeSet
     * @param string        $stylesLrtFilePath
     * @param string        $stylesRtlFilePath
     * @param string        $customScss
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManager $em,
        ThemeSet $editThemeSet,
        $stylesLrtFilePath,
        $stylesRtlFilePath,
        $customScss
    ) {
        $this->em = $em;
        if (!$this->stylesLrtFilePath = realpath($stylesLrtFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesLrtFilePath}");
        }
        if (!$this->stylesRtlFilePath = realpath($stylesRtlFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesRtlFilePath}");
        }
        $this->customScss   = $customScss;
        $this->editThemeSet = $editThemeSet;
    }

    /**
     * Recompile portal css.
     *
     * @param array $variables
     */
    public function recompile(array $variables)
    {
        $themeSet = $this->editThemeSet;
        $themeSet->setOption(self::$customVarsThemeSetOption, $variables);

        $this->em->persist($themeSet);
        $this->em->flush();

        $this->doRecompile('LTR', $variables);
        $this->doRecompile('RTL', $variables);
    }

    /**
     * Recompile portal css.
     *
     * @param string $direction LTR or RTL
     * @param array  $variables
     */
    private function doRecompile($direction, array $variables)
    {
        $themeSet = $this->editThemeSet;

        $css = $this->compileCss($direction, $variables);

        // Find existing or create a new blob storage for the custom Css
        if (!$blobStorage = $this->getEditThemeSetCssBlobStorage($direction)) {
            $blob = new Blob();
            $blob->setFilename($direction === 'RTL' ? 'portal-rtl.css' : 'portal.css');
            $blob->blob_hash = md5($css);
            $this->em->persist($blob);
            $this->em->flush();

            $asset = new ThemeSetAsset();
            $asset->setName($direction === 'RTL' ? 'portal-rtl.css' : 'portal.css');
            $asset->setThemeSet($themeSet);
            $asset->setTags([$direction === 'RTL' ? 'portal_rtl_css' : 'portal_css']);
            $asset->setBlob($blob);
            $this->em->persist($asset);
            $this->em->flush();
        } else {
            // Saving in a new BlobStorage instance to use its' $id as CSS version to bypass caches
            $blob = $this->getEditThemeSetCssBlob($direction);
            $this->em->remove($blobStorage);
            $this->em->flush();
        }

        $blobStorage = new BlobStorage();
        $blobStorage->setBlobId($blob->getId());
        $blobStorage->setData($css);
        $this->em->persist($blobStorage);
        $this->em->flush();
    }

    /**
     * @param string $direction LTR or RTL
     *
     * @return BlobStorage|null
     */
    public function getEditThemeSetCssBlobStorage($direction)
    {
        if ($blob = $this->getEditThemeSetCssBlob($direction)) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }

        return;
    }

    /**
     * Compile portal css from scss sources.
     *
     * @param string $direction LTR or RTL
     * @param array  $variables
     *
     * @return string
     */
    private function compileCss($direction, array $variables)
    {
        $compiler = new StylesheetCompiler();

        return $compiler->compile(
            $direction === 'RTL' ? $this->stylesRtlFilePath : $this->stylesLrtFilePath,
            $variables,
            $this->customScss
        );
    }

    /**
     * @param string $direction LTR or RTL
     *
     * @return Blob|null
     */
    private function getEditThemeSetCssBlob($direction)
    {
        $criteria = [
            'theme_set' => $this->editThemeSet,
            'name'      => $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }
}
