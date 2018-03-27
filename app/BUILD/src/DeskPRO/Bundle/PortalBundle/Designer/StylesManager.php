<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;

/**
 * Class StylesManager.
 */
class StylesManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSet
     */
    private $themeSet;

    /**
     * @var ThemeSet
     */
    private $editThemeSet;

    /**
     * @var ThemeSetCopyingService
     */
    private $themeSetCopyingService;

    /**
     * @var SassDocParser
     */
    private $sassDocParser;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param ThemeSetCopyingService $themeSetCopyingService
     * @param SassDocParser          $sassDocParser
     * @param ThemeSet               $themeSet
     * @param ThemeSet               $editThemeSet
     */
    public function __construct(
        EntityManager          $em,
        ThemeSetCopyingService $themeSetCopyingService,
        SassDocParser          $sassDocParser,
        ThemeSet               $themeSet,
        ThemeSet               $editThemeSet
    ) {
        $this->em                     = $em;
        $this->themeSetCopyingService = $themeSetCopyingService;
        $this->sassDocParser          = $sassDocParser;
        $this->themeSet               = $themeSet;
        $this->editThemeSet           = $editThemeSet;
    }

    /**
     * @param bool $addDefault
     *
     * @return array
     */
    public function getEditThemeSetVariableValues($addDefault = true)
    {
        $values = $this->editThemeSet->getOption(PortalStylesCompiler::$customVarsThemeSetOption, []);
        if ($addDefault) {
            $values = array_merge($this->sassDocParser->getVariableValues(), $values);
        }

        return $values;
    }

    /**
     * Commit changes of the EditThemeSet.
     */
    public function commitEditThemeSet()
    {
        $this->themeSetCopyingService->copy($this->editThemeSet, $ts = $this->themeSet);
        $this->em->persist($ts);
        $this->em->flush();
    }

    /**
     * Discard changes of the EditThemeSet.
     */
    public function discardEditThemeSet()
    {
        $this->themeSetCopyingService->copy($this->themeSet, $ts = $this->editThemeSet);
        $this->em->persist($ts);
        $this->em->flush();
    }

    /**
     * @param string $direction Stylesheet for which direction? LTR or RTL
     *
     * @throws \Exception
     *
     * @return Blob|null
     */
    public function getCssBlob($direction = 'LTR')
    {
        $direction = strtoupper($direction);

        $criteria = [
            'theme_set' => $this->themeSet,
            'name'      => $direction === 'RTL' ? 'portal-rtl.css' : 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }

    /**
     * @param string $direction Stylesheet for which direction? LTR or RTL
     *
     * @throws \Exception
     *
     * @return Blob|null
     */
    public function getEditThemeSetCssBlob($direction = 'LTR')
    {
        $direction = strtoupper($direction);

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
