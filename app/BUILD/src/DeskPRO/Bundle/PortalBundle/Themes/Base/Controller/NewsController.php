<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    /**
     * @Tag(name="news_cats_tabs", default_options={"style":"tabs", "from_root": true}, esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "style": "tabs",
     *          "category": null,
     *          "from_root": false
     *      },
     *      allowed_values={
     *          "style": {"tabs"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options, NewsCategory $category = null)
    {
        $person = $this->getCurrentPerson();

        if ($category) {
            $permissions_bag = $this->getPermissionBag($person);
            if (!$permissions_bag->hasContentCategoryAccess($category)) {
                return new Response(''); // no access to the category will exclude children
            }
        }

        if ($options['from_root']) {
            $category_children = $this->getNewsDataService()->getCategoryChildren(null, $person);
        } else {
            $category_children = $this->getNewsDataService()->getCategoryChildren($category, $person);
        }

        if (empty($category_children)) {
            return new Response(''); // nothing to display here
        }

        return $this->renderThemeView(
            sprintf('Theme:News:CategoryList/%s.html.twig', $options['style']),
            [
                'category'          => $category,
                'category_children' => $category_children,
            ]
        );
    }

    /**
     * @Tag(name="news_list_excerpts", default_options={"style":"excerpts"})
     * @Tag(name="news_list_full", default_options={"style":"full", "show_pager":true}, allow_route_params=true)
     * @Tag(name="news_list_simple", default_options={"style":"simple"}, esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "excerpts",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": false,
     *          "show_pager": false
     *      },
     *      allowed_values={
     *          "style": {"excerpts", "full", "simple"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function listAction(TagRequest $tag_request, array $options, NewsCategory $category = null)
    {
        $person = $this->getCurrentPerson();
        $pager  = $this->getNewsDataService()->getNewsPager(
            $category,
            (int) $options['page'],
            (int) $options['count'],
            $person
        );

        return $this->renderThemeView(
            sprintf('Theme:News:PostList/%s.html.twig', $options['style']),
            [
                'pager'              => $pager,
                'category'           => $category,
                'show_category_link' => $options['show_category_link'],
                'show_pager'         => $options['show_pager'],
            ]
        );
    }

    /**
     * @Tag(name="news_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "post": null
     *      },
     *      allowed_types={
     *          "post":{"Application\DeskPRO\Entity\News","int","string"}
     *      },
     *      attribute_expressions={
     *          "post": "service('data.news').getPost(options['post'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, News $post)
    {
        $comments = $this->getNewsDataService()->getPostComments($post, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
