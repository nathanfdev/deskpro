<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\CommunityForum;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to community forums.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_forums")
 * @ApiDoc(target="all", section="Community", output="Application\DeskPRO\Entity\CommunityForum")
 */
class CommunityForumsController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = CommunityForum::class;
    public static $listSort   = 'title';
    public static $listOrder  = 'asc';

    /**
     * Post a new splash image.
     *
     * You have to post json_encoded object, please refer to the JS code to find out the shape.
     *
     * @ApiDoc(
     *     section="Community",
     *     description="set Splash Image",
     *     requirements={
     *          {
     *              "name"="forum",
     *              "requirement"="\d+",
     *              "description"="the id of forum",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="object",
     *     output="object",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/{forum}/splash_image")
     *
     * @param Request        $request
     * @param CommunityForum $forum
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return JsonResponse
     */
    public function selectSplashImageAction(Request $request, CommunityForum $forum)
    {
        $splashImage = new SplashImageProperty();
        $image       = json_decode($request->request->get('image'));
        $splashImage->setUrn($splashImage::$unsplashNs.':'.$image->id);
        $splashImage->setOptions(['url' => $image->urls->raw]);
        $this->getManager()->persist($splashImage);
        $forum->setSplashImage($splashImage);
        $this->getManager()->flush();
        // Trigger Download on unsplash api to register photo usage
        $client = new Client();
        $client->request('GET', $image->links->download);

        return new JsonResponse($image);
    }

    /**
     * @Rest\Post("/{forum}/splash_image_upload")
     *
     * @param Request        $request
     * @param CommunityForum $forum
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return JsonResponse
     */
    public function uploadSplashImageAction(Request $request, CommunityForum $forum)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorMessage());
        }
        /** @var DeskproBlobStorage $blobStorage */
        $blobStorage = $this->get('deskpro.blob_storage');
        $blob        = $blobStorage->createBlobRecordFromFile(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getMimeType()
        );

        $splashImage = new SplashImageProperty();
        $splashImage->setBlob($blob);
        $splashImage->setUrn(SplashImageProperty::$blobNs.':'.$blob->getAuthId());
        $forum->setSplashImage($splashImage);

        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $blob->getThumbnailUrl(200, true)]);
    }

    /**
     * @Rest\Delete("/{forum}/splash_image")
     *
     * @param CommunityForum $forum
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function deleteSplashImageAction(CommunityForum $forum)
    {
        $splashImage = $forum->getSplashImage();
        if ($splashImage) {
            $this->getManager()->remove($splashImage);
            $forum->setSplashImage(null);
            $this->getManager()->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
