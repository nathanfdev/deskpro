<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Form\Type\IconPropertyType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\ArticleType;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;
use Exception;

/**
 * Class ArticlesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/articles")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Article")
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
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\ArticleType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Article"
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ArticlesController extends AbstractContentController
{
    public static $entity   = Article::class;
    public static $category = ArticleCategory::class;
    public static $type     = ArticleType::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $category = $request->get('category');
        $brands   = $request->query->get('brands');

        if ($category || $brands) {
            $qb->leftJoin("$alias.categories", 'artToCat');
            $qb->leftJoin('artToCat.category', 'cat');
        }

        if ($category) {
            $qb->andWhere('cat.id IN (:category)');
            $qb->setParameter('category', $category);
        }
        if ($brands) {
            $qb->andWhere('cat.brand IN (:brand_ids)');
            $qb->setParameter('brand_ids', $brands);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'category') {
            $qb
                ->addSelect('cat.id as group_name')
                ->addSelect('cat.title as title')
                ->leftJoin("$alias.categories", 'artToCat')
                ->leftJoin('artToCat.category', 'cat')
                ->groupBy('group_name')
            ;
        } else {
            parent::applyListGroupBy($qb, $alias, $groupBy, $request);
        }
    }

    /**
     * Set icon for article.
     *
     *
     * @ApiDoc(
     *     section="Articles",
     *     description="set Icon",
     *     requirements={
     *          {
     *              "name"="article",
     *              "requirement"="\d+",
     *              "description"="the id of article",
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
     * @Rest\Post("/{article}/icon")
     *
     * @param Request $request
     * @param Article $article
     * @return View|JsonResponse
     *
     * @throws ConnectionException
     */
    public function setIconAction(Request $request, Article $article)
    {
        $form = $this->createForm(IconPropertyType::class);

        $form->submit($request->request->all());
        $this->getManager()->getConnection()->beginTransaction();
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $icon = $this->get('images_service')->setIconBlob($form->getData());
                $article->setIcon($icon);
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
     *     section="Articles",
     *     description="Create Splash Image For Article",
     *     requirements={
     *          {
     *              "name"="article",
     *              "requirement"="\d+",
     *              "description"="the id of article",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     * @Rest\Post("/{article}/splash_image_upload")
     * @param Request $request
     * @param Article $article
     * @return JsonResponse
     * @throws OptimisticLockException
     */
    public function uploadSplashImageAction(Request $request, Article $article)
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

        $article->setSplashImage($splashImage);
        $this->getManager()->persist($splashImage);
        $this->getManager()->flush();

        return new JsonResponse(['image' => $splashImage->getBlob()->getThumbnailUrl(200, true)]);
    }

    /**
     * Set splash image for article.
     *
     *
     * @ApiDoc(
     *     section="Articles",
     *     description="set Splash Image",
     *     requirements={
     *          {
     *              "name"="article",
     *              "requirement"="\d+",
     *              "description"="the id of article",
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
     * @Rest\Post("/{article}/splash_image")
     *
     * @param Request $request
     * @param Article $article
     * @return JsonResponse
     *
     * @throws OptimisticLockException
     */
    public function selectSplashImageAction(Request $request, Article $article)
    {
        $image = json_decode($request->request->get('image'));

        $splashImage = $this->get('images_service')->setSplashImage($image);

        if($splashImage instanceof \Exception){
            throw new \RuntimeException($splashImage->getMessage());
        }

        $article->setSplashImage($splashImage);
        $this->getManager()->flush();

        return new JsonResponse($image);
    }


    /**
     * @Rest\Delete("/{article}/splash_image")
     *
     * @param Article $article
     * @return View
     * @throws OptimisticLockException
     */
    public function deleteSplashImageAction(Article $article)
    {
        $splashImage = $article->getSplashImage();
        if ($splashImage) {
            $this->getManager()->remove($splashImage);
            $article->setSplashImage(null);
            $this->getManager()->flush();
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
            'person'          => $this->getUser(),
        ]);

        if ($model->getReviewInterval()) {
            $options['with_review_date'] = true;
        }

        return parent::handleForm($model, $request, $options);
    }
}
