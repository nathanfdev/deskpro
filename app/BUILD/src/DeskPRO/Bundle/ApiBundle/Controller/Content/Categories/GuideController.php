<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\GuideType;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Util\Arrays;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Class GuideController.
 *
 * @Feature("guides")
 * @ApiModes("all")
 * @Rest\Route("/guides")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Guide")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\GuideType",
 *      "options"={"data"="Application\DeskPRO\Entity\Guide"}
 *     }
 * )
 */
class GuideController extends AbstractCategoriesController
{
    public static $entity = Guide::class;
    public static $type   = GuideType::class;

    /**
     * @ApiDoc(
     *     description="give a topics tree list of a guide",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     output="array"
     * )
     * @Rest\Get("/{guideId}/tree")
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Guide $guide
     *
     * @return JsonResponse
     */
    public function getTreeAction(Guide $guide)
    {
        $results = $this->getManager()->getConnection()->fetchAllKeyed('
                SELECT t.id, t.title, IFNULL(t.parent_id, 0) as parent_id, status, hidden_status
                FROM topics t
                WHERE t.guide_id = ?
                ORDER BY display_order ASC
            ', [$guide->getId()], 'id');

        return new JsonResponse(self::objectToArray(Arrays::intoHierarchy($results)));
    }

    /**
     * @ApiDoc(
     *     description="save the tree structure",
     *     statusCodes={
     *         201="Created",
     *         400="Bad Request"
     *     },
     *     parameters={
     *         {"name"="tree", "description"="", "dataType"="array", "required"=true}
     *     }
     * )
     * @Rest\Put("/{guideId}/tree")
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Request $request
     * @param Guide   $guide
     *
     * @return Response
     */
    public function putTreeAction(Request $request, Guide $guide)
    {
        if (!$this->getUser()->PermissionsManager->PublishChecker->canEdit($guide)) {
            throw $this->createAccessDeniedException();
        }
        $tree = $request->request->get('tree');

        $tree = self::fixParentId($tree);

        $tree = Arrays::flattenHierarchy($tree);

        $em = $this->getManager();

        foreach ($tree as $topic) {
            $q = $em->createQuery('
              UPDATE '.Topic::class.' t 
              SET 
                t.display_order = :display_order, t.parent = :parent_id WHERE t.id = :id');
            $q->execute([
                'display_order' => $topic['display_order'],
                'parent_id'     => $topic['parent_id'] ? $topic['parent_id'] : null,
                'id'            => $topic['id'],
            ]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private static function objectToArray($topics)
    {
        foreach ($topics as &$topic) {
            if (!empty($topic['children'])) {
                $topic['children'] = self::objectToArray($topic['children']);
            }
        }

        return array_values($topics);
    }

    private static function fixParentId($topics, $parentId = 0)
    {
        $displayOrder = 1;
        foreach ($topics as &$topic) {
            if (!empty($topic['children'])) {
                $topic['children'] = self::fixParentId($topic['children'], $topic['id']);
            }
            $topic['parent_id']     = $parentId;
            $topic['display_order'] = $displayOrder++;
        }

        return $topics;
    }

    /**
     * @ApiDoc(
     *      description="export a full json of the guide",
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *     output="Application\DeskPRO\Entity\Guide"
     * )
     * @Rest\Get("/{guideId}/export")
     * @Rest\View(serializerGroups={"list", "details"})
     * @ParamConverter("guide", class="DeskPRO:Guide", options={"id" = "guideId"})
     *
     * @param Guide $guide
     *
     * @return \DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper
     */
    public function exportAction(Guide $guide)
    {
        return $this->wrap($guide);
    }

    /**
     * @ApiDoc(
     *     section="Guides",
     *     description="Upload Guide Splash Image",
     *     requirements={
     *          {
     *              "name"="guide",
     *              "requirement"="\d+",
     *              "description"="the id of guide",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     * @Rest\Post("/{guide}/splash_image_upload")
     * @param Request $request
     * @param Guide $guide
     * @return JsonResponse
     * @throws OptimisticLockException
     */
    public function uploadSplashImageAction(Request $request, Guide $guide)
    {
        $file = $request->files->get('file');

        $errorList = $this->get('validator')->validateValue($file, new Image());

        if (count($errorList) > 0) {
            throw new \RuntimeException($errorList[0]->getMessage());
        }

        $splashImage = $this->get('splash_images_service')->createSplashImage($request->files->get('file'));

        if ($splashImage instanceof \Exception) {
            throw new \RuntimeException($splashImage->getMessage());
        }

        $guide->setSplashImage($splashImage);
        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $splashImage->getBlob()->getThumbnailUrl(200, true)]);
    }

    /**
     * Set splash image for guide.
     *
     *
     * @ApiDoc(
     *     section="Guides",
     *     description="set Splash Image",
     *     requirements={
     *          {
     *              "name"="forum",
     *              "requirement"="\d+",
     *              "description"="the id of guide",
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
     * @Rest\Post("/{guide}/splash_image")
     *
     * @param Request $request
     * @param Guide $guide
     *
     * @return JsonResponse
     *
     * @throws OptimisticLockException
     */
    public function selectSplashImageAction(Request $request, Guide $guide)
    {
        $image = json_decode($request->request->get('image'));

        $splashImage = $this->get('splash_images_service')->setSplashImage($image);

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $guide->setSplashImage($splashImage);
        $this->getManager()->flush();

        return new JsonResponse($image);
    }


    /**
     * @Rest\Delete("/{guide}/splash_image")
     *
     * @param Guide $guide
     * @return View
     * @throws OptimisticLockException
     */
    public function deleteSplashImageAction(Guide $guide)
    {
        $splashImage = $guide->getSplashImage();
        if ($splashImage) {
            $this->getManager()->remove($splashImage);
            $guide->setSplashImage(null);
            $this->getManager()->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
