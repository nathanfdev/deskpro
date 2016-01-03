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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ThemeSet $source
     * @param ThemeSet $destination
     *
     * @return string
     */
    public function copy(ThemeSet $source, ThemeSet $destination)
    {
        $destination->setThemeId($source->getThemeId());
        $destination->setOptions($source->getOptions());

        $this->dropThemeSetAssets($destination);
        $this->cloneThemeSetAssets($source, $destination);

        $this->em->persist($destination);
    }

    /**
     * @param ThemeSet $theme_set
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
     */
    private function cloneThemeSetAssets(ThemeSet $source, ThemeSet $destination)
    {
        /** @var ThemeSetAsset[] $assets */
        $assets = $this->em->getRepository(ThemeSetAsset::class)->findBy(['theme_set' => $source]);
        foreach ($assets as $asset) {
            $this->cloneThemeSetAsset($asset, $destination);
        }
    }

    /**
     * @param ThemeSetAsset $asset
     * @param ThemeSet      $theme_set
     *
     * @return ThemeSetAsset
     */
    private function cloneThemeSetAsset(ThemeSetAsset $asset, ThemeSet $theme_set)
    {
        $clone = clone $asset;
        if ($blob = $asset->getBlob()) {
            $clone->setBlob($this->cloneBlob($blob), $clone);
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
        // Clone blob
        $clone = clone $blob;
        $this->em->persist($clone);
        $this->em->flush();

        // Clone storage
        if ($storage = $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()])) {
            /** @var BlobStorage $storage_clone */
            $storage_clone          = clone $storage;
            $storage_clone->blob_id = $clone->getId();
            $this->em->persist($storage_clone);
        }
        $this->em->flush();

        return $clone;
    }
}
