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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AssetsManager.
 */
class AssetsManager
{
    const CUSTOM_ASSET_TAG = 'custom_asset';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param EntityManager   $em
     * @param ThemeSet        $edit_theme_set
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, ThemeSet $edit_theme_set, RouterInterface $router)
    {
        $this->em             = $em;
        $this->edit_theme_set = $edit_theme_set;
        $this->router         = $router;
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
    public function deleteEditThemeSetAsset(ThemeSetAsset $asset)
    {
        $this->em->remove($asset);
        $this->em->flush();
    }

    /**
     * @param UploadedFile $file
     *
     * @return ThemeSetAsset
     */
    public function uploadEditThemeSetAsset(UploadedFile $file)
    {
        $name = uniqid().'_'.$file->getClientOriginalName();
        $name = preg_replace('/\s+/', '_', $name);

        $asset = new ThemeSetAsset();
        $asset->setThemeSet($this->edit_theme_set);
        $asset->setName($name);
        $asset->setBlob($blob = new Blob());
        $asset->setTags([self::CUSTOM_ASSET_TAG]);

        $blob->filename     = $name;
        $blob->blob_hash    = md5_file($file->getRealPath());
        $blob->content_type = $file->getClientMimeType();

        $this->em->persist($blob);
        $this->em->persist($asset);
        $this->em->flush();

        $storage          = new BlobStorage();
        $storage->blob_id = $blob->getId();
        $storage->data    = file_get_contents($file->getRealPath());
        $this->em->persist($storage);
        $this->em->flush();

        return $asset;
    }

    /**
     * @param ThemeSetAsset $asset
     *
     * @return string
     */
    public function getAssetUrl(ThemeSetAsset $asset)
    {
        return $this->router->generate('dp_portal_custom_asset', ['name' => $asset->getName()], true);
    }

    /**
     * @param string $name
     *
     * @return BlobStorage|null
     */
    public function getAssetBlobStorage($name)
    {
        /** @var ThemeSetAsset $asset */
        $asset = $this->em->getRepository(ThemeSetAsset::class)->findOneBy(compact('name'));
        if ($asset && $blob = $asset->getBlob()) {
            return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
        }

        return;
    }
}
