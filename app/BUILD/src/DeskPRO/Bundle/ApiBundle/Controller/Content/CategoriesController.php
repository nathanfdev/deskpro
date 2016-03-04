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

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoriesController.
 *
 * @ApiModes("all")
 */
class CategoriesController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get categories for articles, news and downloads",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/content_categories", name="api_content_categories")
     */
    public function getCategoriesGroupedByContentTypeAction()
    {
        /*
         * DataTransformers arn't applicable if wrap objects in arrays, so using this function to select needed data
         *
         * @todo DataTransformers need fixing?
         *
         * @param \Application\DeskPRO\Entity\CategoryAbstract|array $categories
         * @return array
         */
        $reduce = function (array $categories) {
            $reduced = [];
            foreach ($categories as $category) {
                $reduced[] = ['id' => $category->getId(), 'title' => $category->getTitle()];
            }

            return $reduced;
        };

        $categories = [
            'articles'  => $reduce($this->getRepository(ArticleCategory::class)->findAll()),
            'news'      => $reduce($this->getRepository(NewsCategory::class)->findAll()),
            'downloads' => $reduce($this->getRepository(DownloadCategory::class)->findAll()),
        ];

        return View::create(
            $this->dataSerialize($categories),
            Response::HTTP_OK
        );
    }
}
