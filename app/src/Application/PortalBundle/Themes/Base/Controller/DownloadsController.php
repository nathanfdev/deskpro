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


use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class DownloadsController extends AbstractController
{
    /**
     * @Tag(name="downloads", esi=true)
     * @Tag(name="downloads_list", default_options={"style":"list"}, esi=true)
     * @Cache(smaxage="10 minutes")
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
     * @Tag(name="download", esi=true)
     * @Cache(smaxage="10 minutes")
     *
     * @TagOptions(
     *      defaults={"is_subscribed":false},
     *      required={"file"},
     *      allowed_types={
     *          "file": {"Application\DeskPRO\Entity\Download", "int", "string", "null"},
     *          "is_subscribed": {"int","string","bool"}
     *      }
     * )
     */
    public function fileAction(TagRequest $tag_request, array $options)
    {
        $file = $this->getDownloadsDataService()->getDownload($options['file']);
        $is_subscribed = $options['is_subscribed'];

        return $this->renderThemeView(
            'Theme:Downloads:Tag/download.html.twig',
            array(
                'file' => $file,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Tag(name="download_subscription", esi=true, always_guest_inline=true)
     * @Tag(name="download_subscription_info", default_options={"style":"info"}, esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      required={"file"},
     *      defaults={"style":"link"},
     *      allowed_types={
     *          "file": {"Application\DeskPRO\Entity\File", "int", "string", "null"}
     *      }
     * )
     */
    public function fileSubscriptionAction(TagRequest $tag_request, array $options)
    {
        $file = $this->getDownloadsDataService()->getDownload($options['file']);

        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($file, $this->getUser());
        }

        return $this->renderThemeView(
            sprintf('Theme:Downloads:Tag/download_subscription_%s.html.twig', $options['style']),
            array(
                'file' => $file,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Tag(name="download_category_subscription", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"category": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\DownloadCategory", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function subscriptionsCategoryAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getDownloadsDataService()->getCategory($options['category']);

        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        return $this->renderThemeView(
            'Theme:Downloads:Tag/subscription_category.html.twig', array(
                'category' => $category,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Tag(name="downloads_comments")
     *
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
     * @Tag(name="download_breadcrumbs", esi=true)
     * @Cache(smaxage="10 minutes")
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

    /**
     * @Tag(name="downloads_ratings", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"file": null},
     *      allowed_types={
     *          "file": {"Application\DeskPRO\Entity\Download", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function ratingsAction(TagRequest $tag_request, array $options)
    {
        $file = $this->getDownloadsDataService()->getDownload($options['file']);
        $rating = $this->getRatingsHelper()->getPersonRating($file, $this->getUser());

        return $this->renderThemeView(
            'Theme:Downloads:Tag/ratings.html.twig',
            array(
                'rating' => $rating,
                'file' => $file
            )
        );
    }
}
