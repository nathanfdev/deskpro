<?php



namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class TemplatesController.
 */
class TemplatesController extends AbstractApiController
{
    use HelperMethods;

    public static $tags = [
        'agent_bar'                       => 'Theme:Common:agent_bar.html.twig',
        'agents_online'                   => 'Theme:Sidebar:AgentsOnline/list.html.twig',
        'agents_online_small'             => 'Theme:Sidebar:AgentsOnline/small.html.twig',
        'alerts'                          => 'Theme:Common:alerts.html.twig',
        'breadcrumbs'                     => 'Theme:Common:breadcrumbs.html.twig',
        'chat_pager'                      => 'Theme:Common:comment_form.html.twig',
        'comment_form'                    => 'Theme:Common:comment_form.html.twig',
        'customer_satisfaction'           => 'Theme:Sidebar:Satisfaction/icons.html.twig',
        'download_cat_subscription_link'  => 'Theme:Downloads:CategoryList/subscription_link.html.twig',
        'download_cats'                   => 'Theme:Downloads:CategoryList/detail.html.twig',
        'download_cats_detail'            => 'Theme:Downloads:CategoryList/detail.html.twig',
        'download_cats_simple'            => 'Theme:Downloads:CategoryList/simple.html.twig',
        'download_comments'               => 'Theme:Common:comments.html.twig',
        'download_list_detail'            => 'Theme:Downloads:DownloadList/detail.html.twig',
        'download_list_simple'            => 'Theme:Downloads:DownloadList/simple.html.twig',
        'download_pager'                  => 'Theme:Common:pager.html.twig',
        'download_ratings'                => 'Theme:Downloads:DownloadView/ratings.html.twig',
        'download_root_subscription_link' => 'Theme:Downloads/root_subscription_link.html.twig',
        'download_subscription_link'      => 'Theme:Downloads:DownloadView/subscription_link.html.twig',
        'feedback_comments'               => 'Theme:Common:comments.html.twig',
        'feedback_filter_controls'        => 'Theme:Feedback/filter_controls.html.twig',
        'feedback_form'                   => 'Theme:Feedback/form.html.twig',
        'feedback_list_detail'            => 'Theme:Feedback:FeedbackList/detail.html.twig',
        'feedback_list_simple'            => 'Theme:Feedback:FeedbackList/simple.html.twig',
        'feedback_pager'                  => 'Theme:Common:pager.html.twig',
        'feedback_ratings'                => 'Theme:Feedback:FeedbackView/ratings.html.twig',
        'feedback_root_subscription_link' => 'Theme:Feedback/filter_controls.html.twig',
        'feedback_subscription_link'      => 'Theme:Feedback:FeedbackView/subscription_link.html.twig',
        'flashes'                         => 'Theme:Common:flashes.html.twig',
        'footer'                          => 'Theme:Portal/footer.html.twig',
        'kb'                              => 'Theme:Articles:CategoryList/browser.html.twig',
        'kb_article_comments'             => 'Theme:Common:comments.html.twig',
        'kb_attachments'                  => 'Theme:Articles:ArticleView/attachments.html.twig',
        'kb_cat_subscription_link'        => 'Theme:Articles:ArticleList/subscription_link.html.twig',
        'kb_cats'                         => 'Theme:Articles:CategoryList/browser.html.twig',
        'kb_cats_expander'                => 'Theme:Articles:CategoryList/expander.html.twig',
        'kb_cats_simple'                  => 'Theme:Articles:CategoryList/simple.html.twig',
        'kb_list_detail'                  => 'Theme:Articles:ArticleList/detail.html.twig',
        'kb_list_simple'                  => 'Theme:Articles:ArticleList/simple.html.twig',
        'kb_pager'                        => 'Theme:Common:pager.html.twig',
        'kb_ratings'                      => 'Theme:Articles:ArticleView/ratings.html.twig',
        'kb_root_subscription'            => 'Theme:Articles/root_subscription_link.html.twig',
        'kb_subscription_link'            => 'Theme:Articles:ArticleView/subscription_link.html.twig',
        'kb_top_articles'                 => 'Theme:Sidebar:TopArticles/simple.html.twig',
        'kb_top_articles_detail'          => 'Theme:Sidebar:TopArticles/detail.html.twig',
        'nav_buttons'                     => 'Theme:Portal:nav_buttons_small.html.twig',
        'nav_buttons_big'                 => 'Theme:Portal:nav_buttons_big.html.twig',
        'new_ticket_form'                 => 'Theme:NewTicket:new_ticket_form.html.twig',
        'news_attachments'                => 'Theme:News:PostView/attachments.html.twig',
        'news_cats_tabs'                  => 'Theme:News:CategoryList/tabs.html.twig',
        'news_comments'                   => 'Theme:Common:comments.html.twig',
        'news_list_excerpts'              => 'Theme:News:PostList/excerpts.html.twig',
        'news_list_full'                  => 'Theme:News:PostList/full.html.twig',
        'news_list_simple'                => 'Theme:News:PostList/simple.html.twig',
        'news_pager'                      => 'Theme:Common:pager.html.twig',
        'news_ratings'                    => 'Theme:News:PostView/ratings.html.twig',
        'news_root_subscription_link'     => 'Theme:News/root_subscription_link.html.twig',
        'news_sidebar'                    => 'Theme:Sidebar:News/recent.html.twig',
        'news_sidebar_dates'              => 'Theme:Sidebar:News/dates.html.twig',
        'news_subscription_link'          => 'Theme:News:PostView/subscription_link.html.twig',
        'page_top'                        => 'Theme:Portal:Header/top_bar.html.twig',
        'pdf_breadcrumbs'                 => 'Theme:Common:pdf_breadcrumbs.html.twig',
        'related_content'                 => 'Theme:Common:related_content.html.twig',
        'rss_link'                        => 'Theme:Common:rss_link.html.twig',
        'search_and_contact_bar'          => 'Theme:Portal:Header/page_search_box.html.twig',
        'search_bar'                      => 'Theme:Portal:Header/page_search_box.html.twig',
        'sidebar'                         => 'Theme:Portal:sidebar.html.twig',
        'small_user_info'                 => 'Theme:Portal:Header/small_user_info.html.twig',
        'ticket_form'                     => 'Theme:Tickets/ticket_form.html.twig',
        'ticket_reply'                    => 'Theme:Tickets/ticket_reply.html.twig',
        'ticket_timeline'                 => 'Theme:Tickets:timeline/timeline.html.twig',
        'topic_comments'                  => 'Theme:Common:comments.html.twig',
        'topic_list'                      => 'Theme:Guides:TopicList/list.html.twig',
    ];

    /**
     * @Route("/portal/api/style/edit-theme-set/templates")
     * @Method({"GET"})
     */
    public function getTemplatesListAction()
    {
        $templates = [];
        foreach (array_keys($this->getTheme()->getTemplateMap()) as $templateName) {
            $templates[] = [
                'name'      => $templateName,
                'is_custom' => $this->getEditThemeSetTemplate($templateName) ? true : false,
            ];
        }

        return new JsonResponse($templates);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-info")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTemplateSourceAction(Request $request)
    {
        $templateName = $request->get('template');
        if ($template = $this->getEditThemeSetTemplate($templateName)) {
            $source   = $template->getTemplateCode();
            $isCustom = true;
        } else {
            $theme    = $this->getTheme();
            $source   = file_get_contents($this->getThemeResolver()->templatePath($theme, $templateName));
            $isCustom = false;
        }

        return new JsonResponse([
            'source'    => $source,
            'is_custom' => $isCustom,
        ]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/tag-info")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTagInfoAction(Request $request)
    {
        $tagName = $request->get('tag');
//        $theme = $this->getTheme();
//        $map = $theme->getTemplateMap();
        if (!isset(self::$tags[$tagName])) {
            throw $this->createNotFoundException('Unable to find requested tag');
        }
        $template = self::$tags[$tagName];
//        if ($template = $this->getEditThemeSetTemplate($tagName)) {
//            $source   = $template->getTemplateCode();
//            $isCustom = true;
//        } else {
//            $theme    = $this->getTheme();
//            $source   = file_get_contents($this->getThemeResolver()->templatePath($theme, $tagName));
//            $isCustom = false;
//        }

        return new JsonResponse([
            'template' => $template,
        ]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-sources")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateTemplateSourceAction(Request $request)
    {
        $templateName = $request->get('template');
        if (!$template = $this->getEditThemeSetTemplate($templateName)) {
            $template            = new Template();
            $template->theme_set = $this->getEditThemeSet();
            $template->name      = $templateName;
        }

        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('code', $data) && !array_key_exists('revert', $data)) {
            throw new BadRequestHttpException('Request body must contain "code" or "revert" props');
        }

        try {
            if (!empty($data['revert'])) {
                $this->getManager()->remove($template);
            } else {
                $template->setTemplate(
                    $data['code'],
                    $this->get('twig')->compileSource($data['code'], $templateName)
                );
                $this->getManager()->persist($template);
            }
        } catch (\Twig_Error $e) {
            return new JsonResponse(['error' => $e->getRawMessage(), 'line' => $e->getTemplateLine()]);
        }

        $this->getManager()->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
