<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\NewsType;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Class NewsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/news")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\News")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
 *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
 *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group downloads"},
 *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
 *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="select for article with given id"},
 *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
 *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
 *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
 *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *          {"name"="order_by", "dataType"="integer", "pattern"="date_created|date_updated|person", "description"="how to order"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group counters"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\NewsType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\News"
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class NewsController extends AbstractSingleCategoryContentController
{
    public static $entity   = News::class;
    public static $category = NewsCategory::class;
    public static $type     = NewsType::class;

    /**
     * @ApiDoc(
     *     section="News",
     *     description="Create Splash Image For News",
     *     requirements={
     *          {
     *              "name"="article",
     *              "requirement"="\d+",
     *              "description"="the id of news",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     * @Rest\Post("/{news}/splash_image_upload")
     * @param Request $request
     * @param News $news
     * @return JsonResponse
     * @throws OptimisticLockException
     */
    public function uploadSplashImageAction(Request $request, News $news)
    {
        $file = $request->files->get('file');

        $errorList = $this->get('validator')->validateValue($file, new Image());

        if (count($errorList) > 0) {
            throw new \RuntimeException($errorList[0]->getMessage());
        }

        $splashImage = $this->get('images_service')->createSplashImage($request->files->get('file'));

        if ($splashImage instanceof \Exception) {
            throw new \RuntimeException($splashImage->getMessage());
        }

        $news->setSplashImage($splashImage);
        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $splashImage->getBlob()->getThumbnailUrl(200, true)]);
    }

    /**
     * Set splash image for news.
     *
     *
     * @ApiDoc(
     *     section="News",
     *     description="set Splash Image",
     *     requirements={
     *          {
     *              "name"="news",
     *              "requirement"="\d+",
     *              "description"="the id of news",
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
     * @Rest\Post("/{news}/splash_image")
     *
     * @param Request $request
     * @param News $news
     * @return JsonResponse
     *
     * @throws OptimisticLockException
     */
    public function selectSplashImageAction(Request $request, News $news)
    {
        $image = json_decode($request->request->get('image'));

        $splashImage = $this->get('images_service')->setSplashImage($image);

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $news->setSplashImage($splashImage);
        $this->getManager()->flush();

        return new JsonResponse($image);
    }


    /**
     * @Rest\Delete("/{news}/splash_image")
     *
     * @param News $news
     * @return View
     * @throws OptimisticLockException
     */
    public function deleteSplashImageAction(News $news)
    {
        $splashImage = $news->getSplashImage();
        if ($splashImage) {
            $this->getManager()->remove($splashImage);
            $news->setSplashImage(null);
            $this->getManager()->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
