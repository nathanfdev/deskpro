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
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Component\Util\MapUtils;
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
     * @var DeskproBlobStorage
     */
    private $bs;

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
    private $mainScss;

    /**
     * @var string
     */
    private $customScss;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $bs
     * @param string             $stylesLrtFilePath
     * @param string             $stylesRtlFilePath
     * @param string             $customScss
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManager      $em,
        DeskproBlobStorage $bs,
        $stylesLrtFilePath,
        $stylesRtlFilePath,
        $mainScss,
        $customScss
    ) {
        $this->em = $em;
        $this->bs = $bs;

        if (!$this->stylesLrtFilePath = realpath($stylesLrtFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesLrtFilePath}");
        }
        if (!$this->stylesRtlFilePath = realpath($stylesRtlFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesRtlFilePath}");
        }

        $this->mainScss   = $mainScss;
        $this->customScss = $customScss;
    }

    /**
     * @param ThemeSet $themeSet
     * @param string   $direction Stylesheet for which direction? LTR or RTL
     *
     * @return ThemeSetAsset|null
     */
    public function getCssAsset(ThemeSet $themeSet, $direction = 'LTR')
    {
        $direction = strtoupper($direction);

        $criteria = [
            'theme_set' => $themeSet,
            'name'      => $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css',
        ];

        return $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria);
    }

    /**
     * Check if any vars have changed.
     *
     * @param array    $variables
     * @param ThemeSet $themeSet
     *
     * @return bool
     */
    public function hasChangedVars(array $variables, $themeSet)
    {
        return MapUtils::recursiveDiff(
            $variables,
            $themeSet->getOption(self::$customVarsThemeSetOption)
        ) || MapUtils::recursiveDiff(
            $themeSet->getOption(self::$customVarsThemeSetOption),
            $variables
        );
    }

    /**
     * Recompile portal css.
     *
     * @param array    $variables
     * @param ThemeSet $themeSet
     */
    public function recompile(array $variables, $themeSet)
    {
        $themeSet->setOption(self::$customVarsThemeSetOption, $variables);

        $this->em->persist($themeSet);
        $this->em->flush();

        $this->doRecompile('LTR', $variables, $themeSet);
        $this->doRecompile('RTL', $variables, $themeSet);
    }

    /**
     * Recompile portal css.
     *
     * @param string   $direction LTR or RTL
     * @param array    $variables
     * @param ThemeSet $themeSet
     */
    private function doRecompile($direction, array $variables, ThemeSet $themeSet)
    {
        $css = $this->compileCss($direction, $variables);

        $name = $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css';
        $tag  = $direction === 'RTL' ? 'portal_rtl_css' : 'portal_css';

        $asset   = $this->getCssAsset($themeSet, $direction);
        $oldBlob = null;

        if ($asset) {
            $oldBlob = $asset->getBlob();
        } else {
            $asset = new ThemeSetAsset();
        }

        $blob = $this->bs->createBlobRecordFromString($css, $name, 'text/css', ['tag' => 'brand_asset.'.$tag]);
        $asset->setName($name);
        $asset->setThemeSet($themeSet);
        $asset->setTags([$tag]);
        $asset->setBlob($blob);

        $this->em->persist($asset);
        $this->em->flush();

        if ($oldBlob) {
            $this->bs->deleteBlobRecord($oldBlob);
        }
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
            $this->mainScss,
            $this->customScss
        );
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    public static function parseExceptionMessage(\Exception $e)
    {
        if (preg_match('/^(.*): failed at/', $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return $e->getMessage();
    }
}
