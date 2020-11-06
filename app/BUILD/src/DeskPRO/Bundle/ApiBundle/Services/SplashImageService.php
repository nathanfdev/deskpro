<?php

namespace DeskPRO\Bundle\ApiBundle\Services;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class SplashImageService.
 */
class SplashImageService
{
    /**
     * @var DeskproBlobStorage
     */
    private DeskproBlobStorage $blobStorage;
    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * SplashImageService constructor.
     *
     * @param EntityManager $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $this->em          = $em;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @param UploadedFile $file
     * @return SplashImageProperty|Exception
     */
    public function createSplashImage(UploadedFile $file)
    {
        try {
            $blob = $this->blobStorage->createBlobRecordFromFile(
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getMimeType()
            );

            $splashImage = new SplashImageProperty();
            $splashImage->setBlob($blob);
            $splashImage->setUrn(SplashImageProperty::$blobNs . ':' . $blob->getAuthId());
            $this->em->persist($splashImage);
            $this->em->flush();

            return $splashImage;
        } catch (Exception $e) {
            return $e;
        }
    }
}
