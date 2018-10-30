<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Doctrine\ORM\EntityManager;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Orb\Data\ContentTypes;
use Ossobuffo\PhpIco\IcoConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AssetsManager.
 */
class AssetsManager
{
    const CUSTOM_ASSET_TAG = 'custom_asset';

    const CUSTOM_LOGO_TAG = 'custom_logo';

    const CUSTOM_FAVICON_TAG = 'custom_favicon';

    const CUSTOM_FAVICON_FALLBACK = 'custom_favicon_fallback';

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
        if ($blobAsset = $this->getEditThemeSetBlobAsset($blobType)) {
            if ($blobAsset->getBlob()) {
                $this->bs->deleteBlobRecord($blobAsset->getBlob());
            }
            $this->em->remove($blobAsset);
            $this->em->flush();
        }

        if ($blobType === self::CUSTOM_FAVICON_TAG) {
            if ($blobAsset = $this->getEditThemeSetBlobAsset(self::CUSTOM_FAVICON_FALLBACK)) {
                if ($blobAsset->getBlob()) {
                    $this->bs->deleteBlobRecord($blobAsset->getBlob());
                }
                $this->em->remove($blobAsset);
                $this->em->flush();
            }
        }

        return $blobType === self::CUSTOM_FAVICON_TAG
            ? $this->uploadFavicon($file, $blobType)
            : $this->upload($file, $blobType);
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
            ->orderBy('tsa.id', 'desc')
            ->setMaxResults(1)
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
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorMessage());
        }

        $context = new UploadFileContext($file, $this->edit_theme_set, $tag);
        $name    = uniqid().'_'.$file->getClientOriginalName();
        $name    = preg_replace('/\s+/', '_', $name);
        $context->setName($name);

        return $this->doUpload($context);
    }

    /**
     * @param UploadFileContext $context
     *
     * @return ThemeSetAsset
     */
    private function doUpload(UploadFileContext $context)
    {
        $blob = $this->bs->createBlobRecordFromFile(
            $context->getFilename(),
            $context->getName(),
            $context->getMimeType(),
            ['brand_asset.'.$context->getTag()]
        );
        $context->getAsset()->setBlob($blob);

        $this->em->persist($context->getAsset());
        $this->em->flush();

        return $context->getAsset();
    }

    /**
     * @param UploadedFile $file
     * @param string       $tag
     *
     * @return ThemeSetAsset
     */
    private function uploadFavicon(UploadedFile $file, $tag)
    {
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorMessage());
        }

        $context   = new UploadFileContext($file, $this->edit_theme_set, $tag);
        $extension = ContentTypes::findExtensionForContentType($context->getMimeType());

        if ($extension === 'ico') {
            return $this->upload($file, $tag);
        }

        $imagine = new Imagine();
        $image   = $imagine->open($file->getRealPath());
        $size    = $image->getSize();

        if ($size->getHeight() != $size->getWidth()) {
            // we're about to crop favicon to square shape
            $edge = min($size->getHeight(), $size->getWidth());
            $image->crop(new Point(0, 0), new Box($edge, $edge));
        }

        // allowed types for favicon gif, png
        if (!in_array($extension, ['gif', 'png'])) {
            $context->setExtension('png');
            $context->setMimeType('image/png');
            $name = str_ireplace(".{$extension}", '.png', $context->getName());
            $context->setName($name);
        }

        $filename = sprintf(
            '%s%s.%s',
            $file->getRealPath(),
            uniqid(),
            $context->getExtension()
        );

        $image->save($filename, ['format' => $context->getExtension()]);
        $context->setFilename($filename);

        $this->createFallbackIcon($file, $filename, $context);

        $asset = $this->doUpload($context);
        unlink($filename);

        return $asset;
    }

    /**
     * @param UploadedFile      $file
     * @param string            $filename
     * @param UploadFileContext $previousContext
     */
    private function createFallbackIcon(
        UploadedFile $file,
        $filename, UploadFileContext $previousContext
    ) {
        $context     = new UploadFileContext($file, $this->edit_theme_set, self::CUSTOM_FAVICON_FALLBACK);
        $icoFilename = str_ireplace(".{$previousContext->getExtension()}", '.ico', $filename);
        $name        = str_ireplace(".{$previousContext->getExtension()}", '.ico', $previousContext->getName());
        $context->setFilename($icoFilename);
        $context->setName($name);
        $ico = new IcoConverter($filename, [16, 16]);
        $ico->saveIco($icoFilename);
        $this->doUpload($context);
        unlink($icoFilename);
    }
}
