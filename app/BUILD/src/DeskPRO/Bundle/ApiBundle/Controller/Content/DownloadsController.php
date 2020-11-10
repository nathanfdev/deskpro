<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\Type\IconPropertyType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\DownloadType;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\Validator\Constraints\Image;
use Exception;

/**
 * Class DownloadsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/downloads")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Download")
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
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\DownloadType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Download"
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class DownloadsController extends AbstractSingleCategoryContentController
{
    public static $entity   = Download::class;
    public static $category = DownloadCategory::class;
    public static $type     = DownloadType::class;

    /**
     * Set icon  for download.
     *
     *
     * @ApiDoc(
     *     section="Download",
     *     description="set Icon",
     *     requirements={
     *          {
     *              "name"="download",
     *              "requirement"="\d+",
     *              "description"="the id of download",
     *              "dataType"="integer"
     *          }
     *      },
     *     input="DeskPRO\Bundle\ApiBundle\Form\Type\IconPropertyType",
     *     output="object",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Post("/{download}/icon")
     *
     * @param Request $request
     * @param Download $download
     * @return View|JsonResponse
     *
     * @throws ConnectionException
     */
    public function setIconAction(Request $request, Download $download)
    {
        $form = $this->createForm(IconPropertyType::class);

        $form->submit($request->request->all());
        $this->getManager()->getConnection()->beginTransaction();
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $icon = $this->get('images_service')->setIconBlob($form->getData());
                $download->setIcon($icon);
                $this->getManager()->persist($icon);
                $this->getManager()->flush();
                $this->getManager()->commit();
            }
        } catch (Exception $e) {
            $this->getManager()->getConnection()->rollBack();
            return new JsonResponse($e->getMessage());
        }

        return new View($this->wrap($icon));
    }

    /**
     * @ApiDoc(
     *     section="Downloads",
     *     description="Upload Splash Image For Download",
     *     requirements={
     *          {
     *              "name"="download",
     *              "requirement"="\d+",
     *              "description"="the id of download",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     * @Rest\Post("/{download}/splash_image_upload")
     * @param Request $request
     * @param Download $download
     * @return JsonResponse
     * @throws OptimisticLockException
     */
    public function uploadSplashImageAction(Request $request, Download $download)
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

        $download->setSplashImage($splashImage);
        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $splashImage->getBlob()->getThumbnailUrl(200, true)]);
    }

    /**
     * Set splash image for downloads.
     *
     *
     * @ApiDoc(
     *     section="Downloads",
     *     description="set Splash Image",
     *     requirements={
     *          {
     *              "name"="downloads",
     *              "requirement"="\d+",
     *              "description"="the id of downloads",
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
     * @Rest\Post("/{download}/splash_image")
     *
     * @param Request $request
     * @param Download $download
     * @return JsonResponse
     *
     * @throws OptimisticLockException
     */
    public function selectSplashImageAction(Request $request, Download $download)
    {
        $image = json_decode($request->request->get('image'));

        $splashImage = $this->get('images_service')->setSplashImage($image);

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $download->setSplashImage($splashImage);
        $this->getManager()->flush();

        return new JsonResponse($image);
    }

    /**
     * @Rest\Delete("/{download}/splash_image")
     *
     * @param Download $download
     * @return View
     * @throws OptimisticLockException
     */
    public function deleteSplashImageAction(Download $download)
    {
        $splashImage = $download->getSplashImage();
        if ($splashImage) {
            $this->getManager()->remove($splashImage);
            $download->setSplashImage(null);
            $this->getManager()->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
