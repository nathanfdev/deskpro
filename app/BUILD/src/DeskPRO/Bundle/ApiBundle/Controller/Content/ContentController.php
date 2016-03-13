<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Content\ArticlesCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\ContentCriteria;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentController.
 *
 * @ApiModes("all")
 */
class ContentController extends BaseController
{
    /**
     * With this endpoint you can fetch article`s, news or download`s counts with various filters and grouping sets.
     *
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about content",
     *     description="get articles, news, downloads counts",
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="Returned if your filters was malformed",
     *         404="No route found returned if you provided wrong type",
     *     },
     *     requirements=
     *     {
     *         {
     *             "name"="type",
     *             "requirement"="article|news|download",
     *             "description"="comments type",
     *             "dataType"="string"
     *         }
     *     },
     *     filters={
     *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or me for current user"},
     *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
     *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group counters"},
     *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
     *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
     *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
     *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
     *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
     *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
     *          {"name"="order_by", "dataType"="integer", "pattern"="date_created|date_updated|person", "description"="how to order"},
     *          {"name"="period_updated", "dataType"="integer", "pattern"="asc|desc", "description"="filter by py period when content was updated"},
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Get(
     *     "/{type}/counts",
     *     name="api_content_counts",
     *     requirements={
     *         "type"="articles|news|downloads"
     *     }
     * )
     *
     * @param string  $type
     * @param Request $request
     *
     * @return View
     */
    public function getContentCountsAction($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\ContentCount\ContentCountsDataService $dataService */
        $dataService = $this->get('data.content_counts');

        $params = $this->removeAdditionalParameters($request);
        try {
            /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\BaseContentCriteria $criteria */
            // API interfaces for all content types are identical, however articles is different from
            // news and downloads internally because of Category relation (Article::$categories, while
            // News::$category and Download::$category)
            $criteria = $type === 'articles'
                ? ArticlesCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()])
                : ContentCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $count = $dataService->countContent($this->getClass($type), $criteria);

        return View::create(
            $this->wrap($count),
            Response::HTTP_OK
        );
    }

    /**
     * With this endpoint you can fetch articles lists with different filtering, grouping and sorting options.
     *
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about content",
     *     description="get articles",
     *     statusCodes={
     *        200="Returned if request was successful",
     *        400="Returned if your filters was malformed",
     *        404="No route found returned if you provided wrong type",
     *     },
     *     filters={
     *          {"name"="page", "dataType"="integer", "pattern"="\d+", "description"="which page to display"},
     *          {"name"="count", "dataType"="integer", "pattern"="\d+", "description"="per page articles count"},
     *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
     *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
     *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group articles"},
     *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
     *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="select for article with given id"},
     *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
     *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
     *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
     *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
     *     },
     *     output="array<Application\DeskPRO\Entity\Article>"
     * )
     * @Get("/articles", name="api_content_articles")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listArticlesAction(Request $request)
    {
        return $this->getList('articles', $request);
    }

    /**
     * With this endpoint you can fetch news lists with different filtering, grouping and sorting options.
     *
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about content",
     *     description="get news",
     *     statusCodes={
     *        200="Returned if request was successful",
     *        400="Returned if your filters was malformed",
     *        404="No route found returned if you provided wrong type",
     *     },
     *     filters={
     *          {"name"="page", "dataType"="integer", "pattern"="\d+", "description"="which page to display"},
     *          {"name"="count", "dataType"="integer", "pattern"="\d+", "description"="per page news count"},
     *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
     *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
     *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group news"},
     *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
     *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="select for article with given id"},
     *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
     *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
     *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
     *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
     *     },
     *     output="array<Application\DeskPRO\Entity\News>"
     * )
     *
     * @Get("/news", name="api_content_news")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listNewsAction(Request $request)
    {
        return $this->getList('news', $request);
    }

    /**
     * With this endpoint you can fetch downloads lists with different filtering, grouping and sorting options.
     *
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about content",
     *     description="get downloads",
     *     statusCodes={
     *        200="Returned if request was successful",
     *        400="Returned if your filters was malformed",
     *        404="No route found returned if you provided wrong type",
     *     },
     *     filters={
     *          {"name"="page", "dataType"="integer", "pattern"="\d+", "description"="which page to display"},
     *          {"name"="count", "dataType"="integer", "pattern"="\d+", "description"="per page downloads count"},
     *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
     *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
     *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group downloads"},
     *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
     *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="select for article with given id"},
     *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
     *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
     *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
     *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
     *     },
     *     output="array<Application\DeskPRO\Entity\Download>"
     * )
     *
     * @Get("/downloads", name="api_content_downloads")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listDownloadsAction(Request $request)
    {
        return $this->getList('downloads', $request);
    }

    /**
     * @param         $type
     * @param Request $request
     *
     * @return View
     */
    private function getList($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\ContentSelect\ContentDataService $dataService */
        $dataService = $this->get('data.content');

        $params = $params = $this->removeAdditionalParameters($request);
        try {
            /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\BaseContentCriteria $criteria */
            $criteria = $type === 'articles'
                ? ArticlesCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()])
                : ContentCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page    = $request->query->get('page', 1);
        $count   = $request->query->get('count', 10);
        $content = $dataService->selectContent($this->getClass($type), $criteria, $page, $count);

        return View::create(
            $this->wrap($content),
            Response::HTTP_OK
        );
    }

    /**
     * Get content concrete class full name by content short name.
     *
     * @param string $type
     *
     * @return string
     */
    private function getClass($type)
    {
        $typeToClass = [
            'articles'  => Article::class,
            'news'      => News::class,
            'downloads' => Download::class,
        ];

        return $typeToClass[$type];
    }
}
