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
namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsSelectCriteria;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentsController.
 *
 * @ApiModes("all")
 */
class CommentsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get articles, news, downloads comments counts",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get(
     *     "/{type}_comments/counts",
     *     name="api_content_comments_counts",
     *     requirements={
     *         "type"="article|news|download"
     *     }
     * )
     *
     * @param string  $type
     * @param Request $request
     *
     * @return View
     */
    public function getCommentCountsAction($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsDataService $dataService */
        $dataService = $this->get('data.comments');

        $params = $this->removeAdditionalParameters($request);
        $this->validateParentConsistency($type, $params);
        try {
            /** @var CommentsCountCriteria $criteria */
            $criteria = CommentsCountCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $count = $dataService->countComments($this->getClass($type), $criteria);

        return View::create(
            $this->dataSerialize($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get articles, news, downloads comments list",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get(
     *     "/{type}_comments",
     *     name="api_content_comments",
     *     requirements={
     *         "type"="article|news|download"
     *     }
     * )
     *
     * @param string  $type
     * @param Request $request
     *
     * @return View
     */
    public function listCommentsAction($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsDataService $dataService */
        $dataService = $this->get('data.comments');

        $params = $this->removeAdditionalParameters($request);
        $this->validateParentConsistency($type, $params);
        try {
            /** @var CommentsSelectCriteria $criteria */
            $criteria = CommentsSelectCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);
        $pager = $dataService->selectComments($this->getClass($type), $criteria, $page, $count);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * Validate filters to be consistent with the endpoint.
     *
     * As we use the same controller to handle all comment entities, we need to ensure group_by and filters are
     * consistent with the entity being queried. E.g. the following filters arn't consistent with their endpoints and
     * we want to restrict this queries:
     *
     * /article_comments/counts?news=1          (can't filter article comments by news)
     * /news_comments/counts?download=1         (can't filter news comments by download)
     * /download_comments/counts?group_by=news  (can't group download comments by news)
     *
     * @param string $type
     * @param array  $params
     */
    private function validateParentConsistency($type, array $params)
    {
        // validate group_by

        if (array_key_exists('group_by', $params)) {
            if (!in_array($params['group_by'], [$type, 'status', 'period_created'])) {
                throw new BadRequestHttpException(
                    "You can't group_by \"{$params['group_by']}\" when selecting $type comments."
                );
            }
            unset($params['group_by']);
        }

        // validate filters

        $parentParams = ['article', 'news', 'download'];
        if (($key = array_search($type, $parentParams)) !== false) {
            unset($parentParams[$key]);
        }

        $params = array_keys($params);
        foreach ($parentParams as $parentParam) {
            if (in_array($parentParam, $params)) {
                throw new BadRequestHttpException("$parentParam filter is not allowed when selecting $type comments.");
            }
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getClass($type)
    {
        $typeToClass = [
            'article'  => ArticleComment::class,
            'news'     => NewsComment::class,
            'download' => DownloadComment::class,
        ];

        return $typeToClass[$type];
    }
}
