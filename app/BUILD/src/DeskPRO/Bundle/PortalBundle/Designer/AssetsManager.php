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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AssetsManager.
 */
class AssetsManager
{
    const CUSTOM_ASSET_TAG   = 'custom_asset';
    const CUSTOM_LOGO_TAG    = 'custom_logo';
    const CUSTOM_FAVICON_TAG = 'custom_favicon';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * @var ThemeSet
     */
    private $theme_set;

    /**
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * @param EntityManager      $em
     * @param DeskproBlobStorage $bs
     * @param ThemeSet           $theme_set
     * @param ThemeSet           $edit_theme_set
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $bs, ThemeSet $theme_set, ThemeSet $edit_theme_set)
    {
        $this->em             = $em;
        $this->bs             = $bs;
        $this->theme_set      = $theme_set;
        $this->edit_theme_set = $edit_theme_set;
    }

    /**
     * @return ThemeSetAsset[]
     */
    public function getEditThemeSetAssets()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('tsa')
            ->from(ThemeSetAsset::class, 'tsa')
            ->where('tsa.theme_set = ?0 AND tsa.tags = ?1')
            ->orderBy('tsa.id', 'desc')
            ->setParameters([$this->edit_theme_set, self::CUSTOM_ASSET_TAG]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ThemeSetAsset $asset
     */
    public function deleteEditThemeSetAsset(ThemeSetAsset $asset = null)
    {
        // the asset could be already deleted, skipping
        if (!$asset) {
            return;
        }

        if ($asset->getBlob()) {
            $this->bs->deleteBlobRecord($asset->getBlob());
        }

        $this->em->remove($asset);
        $this->em->flush();

        return;
    }

    /**
     * @param UploadedFile $file
     *
     * @return ThemeSetAsset
     */
    public function uploadEditThemeSetAsset(UploadedFile $file)
    {
        return $this->upload($file, self::CUSTOM_ASSET_TAG);
    }

    /**
     * @param UploadedFile $file
     * @param string       $blobType
     *
     * @return ThemeSetAsset
     */
    public function uploadBlob(UploadedFile $file, $blobType = self::CUSTOM_LOGO_TAG)
    {
        if ($logo = $this->getEditThemeSetBlobAsset($blobType)) {
            if ($logo->getBlob()) {
                $this->bs->deleteBlobRecord($logo->getBlob());
            }
            $this->em->remove($logo);
            $this->em->flush();
        }

        return $this->upload($file, $blobType);
    }

    /**
     * @param string $blobType asset type
     *
     * @return ThemeSetAsset|null
     */
    public function getEditThemeSetBlobAsset($blobType = self::CUSTOM_LOGO_TAG)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('tsa')
            ->from(ThemeSetAsset::class, 'tsa')
            ->where('tsa.theme_set = ?0 AND tsa.tags = ?1')
            ->setParameters([$this->edit_theme_set, $blobType]);

        $asset = $qb->getQuery()->getOneOrNullResult();

        return $asset;
    }

    /**
     * @param $blobType
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ThemeSetAsset|null
     */
    public function getBlobAsset($blobType = self::CUSTOM_LOGO_TAG)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('tsa')
            ->from(ThemeSetAsset::class, 'tsa')
            ->where('tsa.theme_set = ?0 AND tsa.tags = ?1')
            ->setParameters([$this->theme_set, $blobType]);

        $asset = $qb->getQuery()->getOneOrNullResult();

        return $asset;
    }

    /**
     * @param UploadedFile $file
     * @param string       $tag
     *
     * @return ThemeSetAsset
     */
    private function upload(UploadedFile $file, $tag)
    {
        $name = uniqid().'_'.$file->getClientOriginalName();
        $name = preg_replace('/\s+/', '_', $name);

        $asset = new ThemeSetAsset();
        $asset->setThemeSet($this->edit_theme_set);
        $asset->setName($name);
        $asset->setTags([$tag]);

        $blob = $this->bs->createBlobRecordFromFile($file->getRealPath(), $name, $file->getClientMimeType(), ['brand_asset.'.$tag]);
        $asset->setBlob($blob);

        $this->em->persist($asset);
        $this->em->flush();

        return $asset;
    }
}
