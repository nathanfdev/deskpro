<?php

namespace DeskPRO\Bundle\ApiBundle\Services;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Component\Util\IpUtils;
use Doctrine\ORM\EntityManager;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImagesService.
 */
class ImagesService
{
    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var SettingsResolver
     */
    private  $settingResolver;

    /**
     * ImagesService constructor.
     *
     * @param EntityManager $em
     * @param DeskproBlobStorage $blobStorage
     * @param SettingsResolver $settingResolver
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage, SettingsResolver $settingResolver)
    {
        $this->em              = $em;
        $this->blobStorage     = $blobStorage;
        $this->settingResolver = $settingResolver;
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


    /**
     * @param $image
     * @return SplashImageProperty|Exception
     */
    public function setSplashImage($image)
    {
        try {
            $splashImage = new SplashImageProperty();
            $splashImage->setUrn($splashImage::$unsplashNs . ':' . $image['id']);
            $splashImage->setOptions(['url' => $image['urls']['raw']]);
            $this->em->persist($splashImage);
            $this->em->flush();

            $this->triggerSplashImageDownload($image);

            return $splashImage;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * @param $image
     * @return PromiseInterface|Exception
     */
    public function triggerSplashImageDownload($image)
    {
        try {
            $client = new HttpClient();
            if (!IpUtils::isUrlUserCallable($image['links']['download_location'])) {
                throw new \InvalidArgumentException("URL is not user callable");
            }

            return $client->requestAsync('GET', $image['links']['download_location'], [
                'headers' => [
                    'Authorization' => 'Client-ID ' . $this->settingResolver->getGlobalSettings()->get('services.unsplash_access_key',
                            null),
                ],
            ]);

        } catch (Exception $e) {
            return $e;
        }
    }


    /**
     * @param IconProperty $icon
     * @return IconProperty|Exception
     */
    public function setIconBlob(IconProperty $icon)
    {
        try {
            if ($icon->getUrnNs() === IconProperty::$blobNs) {
                $authId = $icon->getUrnPath();
                $blob   = $this->em->getRepository(Blob::class)->getByAuthId($authId);
                if ($blob) {
                    $rawFile = $this->blobStorage->copyBlobRecordToString($blob);
                    $blob    = $this->blobStorage->createBlobRecordFromString(
                        $rawFile,
                        $blob->getFilename(),
                        $blob->getContentType()
                    );
                    $icon->setBlob($blob);
                }
            }

            return $icon;
        } catch (Exception $e) {
            return $e;
        }
    }
}
