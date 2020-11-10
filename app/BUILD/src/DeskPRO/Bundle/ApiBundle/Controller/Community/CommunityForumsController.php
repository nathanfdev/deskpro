<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityForum;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;

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
     * @return JsonResponse
     *@throws OptimisticLockException|JsonException
     *
     */
    public function selectSplashImageAction(Request $request, CommunityForum $forum)
    {
        $image = json_decode($request->request->get('image'));

        $splashImage = $this->get('images_service')->setSplashImage($image);

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $forum->setSplashImage($splashImage);
        $this->getManager()->flush();

        return new JsonResponse($image);
    }

    /**
     * @Rest\Post("/{forum}/splash_image_upload")
     *
     * @param Request        $request
     * @param CommunityForum $forum
     *
     * @throws OptimisticLockException
     *
     * @return JsonResponse
     */
    public function uploadSplashImageAction(Request $request, CommunityForum $forum)
    {
        $file = $request->files->get('file');

        $errorList = $this->get('validator')->validateValue($file, new Image());

        if (count($errorList) > 0) {
            throw new \RuntimeException($errorList[0]->getMessage());
        }

        $splashImage = $this->get('images_service')->createSplashImage( $request->files->get('file'));

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $forum->setSplashImage($splashImage);
        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $splashImage->getBlob()->getThumbnailUrl(200, true)]);
    }

    
    /**
     * @Rest\Delete("/{forum}/splash_image")
     *
     * @param CommunityForum $forum
     *
     * @throws OptimisticLockException
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
