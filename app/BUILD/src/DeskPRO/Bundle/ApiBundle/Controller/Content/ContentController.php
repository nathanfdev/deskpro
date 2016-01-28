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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\DataService\Content\ArticlesCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\ContentCriteria;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentController.
 */
class ContentController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get articles, news, downloads counts",
     *      statusCodes={
     *          200="Success"
     *      }
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
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get articles, news, downloads",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Get(
     *     "/{type}",
     *     name="api_content",
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
    public function getAction($type, Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\ContentSelect\ContentDataService $dataService */
        $dataService = $this->get('data.content');

        $params = $params = $this->removeAdditionalParameters($request);
        try {
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
            $this->dataSerialize($content),
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
