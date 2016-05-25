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
     * @param EntityManager          $em
     * @param ThemeSetCopyingService $themeSetCopyingService
     * @param SassDocParser          $sassDocParser
     * @param ThemeSet               $themeSet
     * @param ThemeSet               $editThemeSet
     */
    public function __construct(
        EntityManager $em,
        ThemeSetCopyingService $themeSetCopyingService,
        SassDocParser $sassDocParser,
        ThemeSet $themeSet,
        ThemeSet $editThemeSet
    ) {
        $this->em                     = $em;
        $this->themeSetCopyingService = $themeSetCopyingService;
        $this->sassDocParser          = $sassDocParser;
        $this->themeSet               = $themeSet;
        $this->editThemeSet           = $editThemeSet;
    }

    /**
     * @param string $direction Stylesheet for which direction? LTR or RTL
     *
     * @return BlobStorage|null
     */
    public function getCssBlobStorage($direction = 'LTR')
    {
        if ($blob = $this->getCssBlob($direction)) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }
    }

    /**
     * @param string $direction Stylesheet for which direction? LTR or RTL
     *
     * @return BlobStorage|null
     */
    public function getEditThemeSetCssBlobStorage($direction = 'LTR')
    {
        if ($blob = $this->getEditThemeSetCssBlob($direction)) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }
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
    private function getCssBlob($direction = 'LTR')
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
    private function getEditThemeSetCssBlob($direction = 'LTR')
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
