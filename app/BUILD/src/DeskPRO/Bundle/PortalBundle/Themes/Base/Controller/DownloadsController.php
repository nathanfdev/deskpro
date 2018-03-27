<?php

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
            [
                'category'          => $category,
                'category_children' => $category_children,
            ]
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

        $pager = $this->getDownloadsDataService()->getDownloadsPager(
            $category,
            (int) $options['page'],
            (int) $options['count'],
            $person
        );

        return $this->renderThemeView(
            sprintf('Theme:Downloads:DownloadList/%s.html.twig', $options['style']),
            [
                'show_pager'         => $options['show_pager'],
                'category'           => $category,
                'pager'              => $pager,
                'show_category_link' => $options['show_category_link'],
            ]
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

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
