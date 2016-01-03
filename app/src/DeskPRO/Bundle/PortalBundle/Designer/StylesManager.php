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
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var ThemeSetCopyingService
     */
    private $theme_set_copying_service;

    /**
     * @var SassDocParser
     */
    private $sass_doc_parser;

    /**
     * @param EntityManager          $em
     * @param ThemeSetCopyingService $theme_set_copying_service
     * @param SassDocParser          $sass_doc_parser
     * @param BrandStack             $brand_stack
     */
    public function __construct(
        EntityManager $em,
        ThemeSetCopyingService $theme_set_copying_service,
        SassDocParser $sass_doc_parser,
        BrandStack $brand_stack
    ) {
        $this->em                        = $em;
        $this->theme_set_copying_service = $theme_set_copying_service;
        $this->brand_stack               = $brand_stack;
        $this->sass_doc_parser           = $sass_doc_parser;
    }

    /**
     * @return BlobStorage|null
     */
    public function getCssBlobStorage()
    {
        if ($blob = $this->getCssBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }
    }

    /**
     * @return BlobStorage|null
     */
    public function getEditThemeSetCssBlobStorage()
    {
        if ($blob = $this->getEditThemeSetCssBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }
    }

    /**
     * @param bool $add_default
     *
     * @return array
     */
    public function getEditThemeSetVariableValues($add_default = true)
    {
        $values = $this->getEditThemeSet()->getOption(PortalStylesCompiler::$custom_vars_theme_set_option, []);
        if ($add_default) {
            $values = array_merge($this->sass_doc_parser->getVariableValues(), $values);
        }

        return $values;
    }

    /**
     * Commit changes of the EditThemeSet.
     */
    public function commitEditThemeSet()
    {
        $this->theme_set_copying_service->copy($this->getEditThemeSet(), $ts = $this->getThemeSet());
        $this->em->persist($ts);
        $this->em->flush();
    }

    /**
     * Discard changes of the EditThemeSet.
     */
    public function discardEditThemeSet()
    {
        $this->theme_set_copying_service->copy($this->getThemeSet(), $ts = $this->getEditThemeSet());
        $this->em->persist($ts);
        $this->em->flush();
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private function getEditThemeSet()
    {
        if (!$themeSet = $this->getBrand()->getEditThemeSet()) {
            $themeSet = new ThemeSet();
            $this->theme_set_copying_service->copy($this->getThemeSet(), $themeSet);
            $brand = $this->getBrand();
            $brand->setEditThemeSet($themeSet);
            $this->em->persist($themeSet);
            $this->em->persist($brand);
            $this->em->flush();
        }

        return $themeSet;
    }

    /**
     * @throws \Exception
     *
     * @return Blob|null
     */
    private function getCssBlob()
    {
        $criteria = [
            'theme_set' => $this->getThemeSet(),
            'name'      => 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }

    /**
     * @throws \Exception
     *
     * @return Blob|null
     */
    private function getEditThemeSetCssBlob()
    {
        $criteria = [
            'theme_set' => $this->getEditThemeSet(),
            'name'      => 'portal.css',
        ];
        if ($asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy($criteria)) {
            return $asset->getBlob();
        }

        return;
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private function getThemeSet()
    {
        if (!$themeSet = $this->getBrand()->getThemeSet()) {
            throw new \Exception('Unable to resolve a theme set');
        }

        return $themeSet;
    }

    /**
     * @throws \Exception
     *
     * @return Brand
     */
    private function getBrand()
    {
        if (!$container = $this->brand_stack->getActive()) {
            throw new \Exception('Unable to resolve the current brand');
        }

        return $container->getBrand();
    }
}
