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
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class DownloadsController extends AbstractController
{
    /**
     * @Tag(name="downloads")
     * @Tag(name="downloads_list", default_options={"style":"list"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "overview",
     *          "category": null
     *      },
     *      allowed_values={
     *          "style": {"list","overview"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\DownloadCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getDownloadsDataService()->getCategory($options['category']);
        $category_children = $this->getDownloadsDataService()->getCategoryChildren($category);

        return $this->renderThemeView(
            sprintf('Theme:Downloads:Tag/%s.html.twig', $options['style']),
            array(
                'category' => $category,
                'category_children' => $category_children
            )
        );
    }


    /**
     * @Tag(name="downloads_files")
     * @Tag(name="downloads_files_simple", default_options={"style":"simple"})
     * @Tag(name="downloads_files_items", default_options={"style":"items"})
     * @Tag(name="downloads_files_list", default_options={"style":"small"})
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "small",
     *          "page": 1,
     *          "count": 10
     *      },
     *      allowed_values={
     *          "style": {"small","simple","items"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\DownloadCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getDownloadsDataService()->getCategory($options['category']);
        $pager = $this->getDownloadsDataService()->getDownloadsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            sprintf('Theme:Downloads:Tag/files_%s.html.twig', $options['style']),
            array(
                'category' => $category,
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="download_comments")
     * @TagOptions(
     *      defaults={
     *          "file": null
     *      },
     *      allowed_types={
     *          "file":{"Application\DeskPRO\Entity\Download","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function commentsAction(TagRequest $tag_request, array $options)
    {
        $file = $this->getDownloadsDataService()->getDownload($options['file']);
        $comments = $this->getDownloadsDataService()->getDownloadComments($file, $this->getUser());

        return $this->renderThemeView('Theme:Downloads:Tag/comments.html.twig', array(
            'file' => $file,
            'comments' => $comments
        ));
    }

    /**
     * @Tag(name="downloads_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "show_pagination": true,
     *          "page": 1,
     *          "count": 2
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\DownloadCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $category = $this->getDownloadsDataService()->getCategory($options['category']);
        $pager = $this->getDownloadsDataService()->getDownloadsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            'Theme:Common:pager.html.twig',
            array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="downloads_breadcrumbs")
     *
     * @TagOptions(
     *      defaults={"category": null, "file": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\DownloadCategory", "int", "string", "null"},
     *          "file": {"Application\DeskPRO\Entity\Download", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function breadcrumbsAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getDownloadsDataService()->getCategory($options['category']);
        $file = $this->getDownloadsDataService()->getDownload($options['file']);

        return $this->renderThemeView(
            'Theme:Downloads:Tag/breadcrumbs.html.twig',
            array(
                'category' => $category,
                'file' => $file
            )
        );
    }
}
