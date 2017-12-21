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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;

/**
 * Class ThemeSetCloningService.
 */
class ThemeSetCopyingService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $this->em          = $em;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @param ThemeSet $source
     * @param ThemeSet $destination
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function copy(ThemeSet $source, ThemeSet $destination)
    {
        $destination->setThemeId($source->getThemeId());
        $destination->setOptions($source->getOptions());
        $this->em->persist($destination);

        $this->cloneThemeSetAssets($source, $destination);

        $this->dropTemplates($destination);
        $this->cloneTemplates($source, $destination);

        $this->em->persist($destination);
    }

    /**
     * @param ThemeSet $theme
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function drop(ThemeSet $theme)
    {
        $this->dropThemeSetAssets($theme);
        $this->dropTemplates($theme);

        $this->em->remove($theme);
        $this->em->flush();
    }

    /**
     * @param ThemeSet $theme_set
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function dropThemeSetAssets(ThemeSet $theme_set)
    {
        $assets = $this->em->getRepository(ThemeSetAsset::class)->findBy(compact('theme_set'));
        foreach ($assets as $asset) {
            $this->em->remove($asset);
        }
        $this->em->flush();
    }

    /**
     * @param ThemeSet $source
     * @param ThemeSet $destination
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cloneThemeSetAssets(ThemeSet $source, ThemeSet $destination)
    {
        $themeSetAssetRepository = $this->em->getRepository(ThemeSetAsset::class);
        /** @var ThemeSetAsset[] $assets */
        $assets            = $themeSetAssetRepository->findBy(['theme_set' => $source]);
        $destinationAssets = $themeSetAssetRepository->findBy(['theme_set' => $destination]);
        foreach ($assets as $asset) {
            $destAsset = current(array_filter($destinationAssets, function ($a) use ($asset) {
                /* @var ThemeSetAsset $a */
                return $a->getName() === $asset->getName();
            }));
            $this->cloneThemeSetAsset($asset, $destination, $destAsset);
        }
    }

    /**
     * @param ThemeSet $theme_set
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function dropTemplates(ThemeSet $theme_set)
    {
        $templates = $this->em->getRepository(Template::class)->findBy(compact('theme_set'));
        foreach ($templates as $template) {
            $this->em->remove($template);
        }
        $this->em->flush();
    }

    /**
     * @param ThemeSet $source
     * @param ThemeSet $destination
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cloneTemplates(ThemeSet $source, ThemeSet $destination)
    {
        /** @var Template[] $templates */
        $templates = $this->em->getRepository(Template::class)->findBy(['theme_set' => $source]);
        foreach ($templates as $template) {
            $clone = clone $template;
            $clone->setThemeSet($destination);
            $this->em->persist($clone);
        }
        $this->em->flush();
    }

    /**
     * @param ThemeSetAsset      $asset
     * @param ThemeSet           $theme_set
     * @param ThemeSetAsset|null $destAsset
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return ThemeSetAsset
     */
    private function cloneThemeSetAsset(ThemeSetAsset $asset, ThemeSet $theme_set, $destAsset = null)
    {
        $destClone = null;
        if ($destAsset) {
            $destClone = $destAsset->getBlob();
            $this->em->remove($destAsset);
        }
        $this->em->flush();
        $clone = clone $asset;
        if ($blob = $asset->getBlob()) {
            if ($destClone && $destClone->getBlobHash() === $blob->getBlobHash()) {
                $clone->setBlob($destClone);
            } else {
                $clone->setBlob($this->cloneBlob($blob));
                // We set the blob as temp to be clean out by a later job
                if ($destClone) {
                    $destClone->setIsTemp(true);
                }
            }
        }
        $clone->setThemeSet($theme_set);
        $this->em->persist($clone);
        $this->em->flush();

        return $clone;
    }

    /**
     * @param Blob $blob
     *
     * @return Blob
     */
    private function cloneBlob(Blob $blob)
    {
        $data = $this->blobStorage->copyBlobRecordToString($blob);

        return $this->blobStorage->createBlobRecordFromString($data, $blob->getFilename(), $blob->getContentType());
    }
}
