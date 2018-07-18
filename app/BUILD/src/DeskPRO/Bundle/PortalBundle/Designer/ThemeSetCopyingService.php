<?php

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
     * @param ThemeSet $fromThemeSet
     * @param ThemeSet $toThemeSet
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cloneThemeSetAssets(ThemeSet $fromThemeSet, ThemeSet $toThemeSet)
    {
        $newAssets = $this->em->getRepository(ThemeSetAsset::class)->findBy(['theme_set' => $fromThemeSet]);
        $oldAssets = $this->em->getRepository(ThemeSetAsset::class)->findBy(['theme_set' => $toThemeSet]);

        // delete old assets
        foreach ($oldAssets as $asset) {
            $this->em->remove($asset);
        }

        $this->em->flush();

        // we don't have unique key for asset name so just
        // prevent copying assets with the same names
        $uniqueAssets = [];
        foreach ($newAssets as $asset) {
            $uniqueAssets[$asset->getName()] = $asset;
        }

        // copy new assets
        foreach ($uniqueAssets as $newAsset) {
            /* @var ThemeSetAsset $oldAsset */
            $oldAsset = current(array_filter($oldAssets, function ($a) use ($newAsset) {
                /* @var ThemeSetAsset $a */
                return $a->getName() === $newAsset->getName();
            }));

            $oldBlob = $oldAsset ? $oldAsset->getBlob() : null;

            $this->cloneThemeSetAsset($newAsset, $toThemeSet, $oldBlob);
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
     * @param ThemeSetAsset $newAsset
     * @param ThemeSet      $toThemeSet
     * @param Blob|null     $oldBlob
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return ThemeSetAsset
     */
    private function cloneThemeSetAsset(ThemeSetAsset $newAsset, ThemeSet $toThemeSet, Blob $oldBlob = null)
    {
        // clone theme asset
        $clonedAsset = clone $newAsset;
        $clonedAsset->setThemeSet($toThemeSet);
        if ($blob = $newAsset->getBlob()) {
            // the blob wasn't changed, just re-use it
            if ($oldBlob && $oldBlob->getBlobHash() === $blob->getBlobHash()) {
                $clonedAsset->setBlob($oldBlob);
            } else {
                // the blob was changed, clone from the new asset
                $clonedAsset->setBlob($this->cloneBlob($blob));
            }
        }

        $this->em->persist($clonedAsset);

        // the old blob was changed and overwritten, mark it as temp to remove by a clean job later
        if ($oldBlob && $clonedAsset->getBlob() !== $oldBlob) {
            $blob->setIsTemp(true);
            $this->em->persist($oldBlob);
        }

        $this->em->flush();

        return $clonedAsset;
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
