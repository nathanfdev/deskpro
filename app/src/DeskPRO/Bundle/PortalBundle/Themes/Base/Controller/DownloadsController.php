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
namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class DownloadsController extends AbstractController
{
    /**
     * @Tag(name="download_cats", default_options={"style":"detail"}, esi=true)
     * @Tag(name="download_cats_detail", default_options={"style":"detail"}, esi=true)
     * @Tag(name="download_cats_simple", default_options={"style":"simple"}, esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "style": "detail",
     *          "category": null
     *      },
     *      allowed_values={
     *          "style": {"simple","detail"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\DownloadCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.downloads').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options, DownloadCategory $category = null)
    {
        $person = $this->getCurrentPerson();

        if ($category) {
            $permissions_bag = $this->getPermissionBag($person);
            if (!$permissions_bag->hasContentCategoryAccess($category)) {
                return new Response(''); // no access to the category will exclude children
            }
        }

        $category_children = $this->getDownloadsDataService()->getCategoryChildren($category, $person);

        if (empty($category_children)) {
            return new Response(''); // nothing to display here
        }

        return $this->renderThemeView(
            sprintf('Theme:Downloads:CategoryList/%s.html.twig', $options['style']),
            array(
                'category'          => $category,
                'category_children' => $category_children,
            )
        );
    }

    /**
     * @Tag(name="download_list_simple", default_options={"style":"simple"}, esi=true)
     * @Tag(name="download_list_detail", default_options={"style":"detail", "show_pager":true}, allow_route_params=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "detail",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": false,
     *          "show_pager": false
     *      },
     *      allowed_values={
     *          "style": {"detail","simple"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\DownloadCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.downloads').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function listAction(TagRequest $tag_request, array $options, DownloadCategory $category = null)
    {
        $person = $this->getCurrentPerson();

        $pager = $this->getDownloadsDataService()->getDownloadsPager($category, $options['page'], $options['count'], $person);

        return $this->renderThemeView(
            sprintf('Theme:Downloads:DownloadList/%s.html.twig', $options['style']),
            array(
                'show_pager'         => $options['show_pager'],
                'category'           => $category,
                'pager'              => $pager,
                'show_category_link' => $options['show_category_link'],
            )
        );
    }

    /**
     * @Tag(name="download_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "file": null
     *      },
     *      allowed_types={
     *          "file":{"Application\DeskPRO\Entity\Download","int","string"}
     *      },
     *      attribute_expressions={
     *          "file": "service('data.downloads').getDownload(options['file'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, Download $file)
    {
        $comments = $this->getDownloadsDataService()->getDownloadComments($file, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', array(
            'comments' => $comments,
        ));
    }
}
