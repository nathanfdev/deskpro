<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
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
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * @var AdvancedEditsManager
     */
    private $advancedEditsManager;

    /**
     * @var string path to the portal SCSS file
     */
    private $stylesLrtFilePath;

    /**
     * @var string path to the portal SCSS file
     */
    private $stylesRtlFilePath;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param DeskproBlobStorage   $bs
     * @param AdvancedEditsManager $advancedEditsManager
     * @param string               $stylesLrtFilePath
     * @param string               $stylesRtlFilePath
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManager $em,
        DeskproBlobStorage $bs,
        AdvancedEditsManager $advancedEditsManager,
        $stylesLrtFilePath,
        $stylesRtlFilePath
    ) {
        $this->em                   = $em;
        $this->bs                   = $bs;
        $this->advancedEditsManager = $advancedEditsManager;

        if (!$this->stylesLrtFilePath = realpath($stylesLrtFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesLrtFilePath}");
        }
        if (!$this->stylesRtlFilePath = realpath($stylesRtlFilePath)) {
            throw new \Exception("Can't resolve a file from the given path: {$this->stylesRtlFilePath}");
        }
    }

    /**
     * @param ThemeSet $themeSet
     * @param string   $direction Stylesheet for which direction? LTR or RTL
     * @param mixed $themeId
     *
     * @return ThemeSetAsset|null
     */
    public function getCssAsset(ThemeSet $themeSet, $direction = 'LTR', $themeId = 'standard')
    {
        $direction = strtoupper($direction);

        if ($themeId === 'helpcenter') {
            $criteria = [
                'theme_set' => $themeSet,
                'name'      => $direction === 'RTL' ? 'helpcenter-rtl.css' : 'helpcenter.css',
            ];
        } else {
            $criteria = [
                'theme_set' => $themeSet,
                'name'      => $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css',
            ];
        }

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
    public function hasChangedVars(array $variables, ThemeSet $themeSet)
    {
        return $variables != $themeSet->getOption(self::$customVarsThemeSetOption, []);
    }

    /**
     * Recompile portal css.
     *
     * @param array    $variables
     * @param ThemeSet $themeSet
     */
    public function recompile(array $variables, ThemeSet $themeSet)
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

        if ($themeSet->getThemeId() === 'helpcenter') {
            $name = $direction === 'RTL' ? 'helpcenter-rtl.css' : 'helpcenter.css';
            $tag  = $direction === 'RTL' ? 'helpcenter_rtl_css' : 'helpcenter_css';
        } else {
            $name = $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css';
            $tag  = $direction === 'RTL' ? 'portal_rtl_css' : 'portal_css';
        }

        $asset   = $this->getCssAsset($themeSet, $direction, $themeSet->getThemeId());
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

        if ($oldBlob) {
            $oldBlob->is_temp      = false;
            $oldBlob->date_created = new \DateTime();
            $this->em->persist($oldBlob);
        }

        $this->em->flush();
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
        // Prevent error when brand-neutral is not set
        $variables = array_merge(['brand-neutral' => '#F8AF3C'], $variables);

        $compiler = new StylesheetCompiler();

        return $compiler->compile(
            $direction === 'RTL' ? $this->stylesRtlFilePath : $this->stylesLrtFilePath,
            $variables,
            $this->advancedEditsManager->getMainScss(),
            $this->advancedEditsManager->getEditThemeSetScss()
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
