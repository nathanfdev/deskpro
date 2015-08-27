<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsCountCriteria;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\DownloadComment;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CommentCountsController
 */
class CommentCountsController extends BaseController
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
     */
    public function getCommentCountsAction($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentCountsDataService $dataService */
        $dataService = $this->get('data.comment_counts');

        $params = $request->query->all();
        $this->validateParentConsistency($type, $params);
        try {
            $criteria = CommentsCountCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $typeToClass = [
            'article'  => ArticleComment::class,
            'news'     => NewsComment::class,
            'download' => DownloadComment::class,
        ];
        $count = $dataService->countComments($typeToClass[$type], $criteria);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * Validate filters to be consistent with the endpoint
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
     * @param array $params
     */
    private function validateParentConsistency($type, array $params)
    {
        // validate group_by

        if (array_key_exists('group_by', $params)) {
            if (!in_array($params['group_by'], [$type, 'status'])) {
                throw new BadRequestHttpException(
                    "You can't group_by \"{$params['group_by']}\" when selecting $type comments.");
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
}
