<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbBuilder
{
    /**
     * @var Breadcrumbs
     */
    private $b;
    /**
     * @var ObjectRouter
     */
    private $object_router;
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(ObjectRouter $object_router, UrlGeneratorInterface $url_generator, LanguageManager $language_manager)
    {
        $this->object_router = $object_router;
        $this->url_generator = $url_generator;
        $this->b             = new Breadcrumbs();
        $this->b->add(
            $this->url_generator->generate('portal_home'),
            Breadcrumbs::PORTAL,
            ['phrase' => 'portal.general.nav-portal']
        );
        $this->language_manager = $language_manager;
    }

    //####################################################################################################################
    // CHAT
    //####################################################################################################################

    public function addChat()
    {
        $this->b->add(
            $this->url_generator->generate('portal_chats'),
            Breadcrumbs::CHAT,
            ['phrase' => 'portal.general.nav-chat']
        );

        return $this;
    }

    public function addChatView(ChatConversation $chat)
    {
        $this->b->add(
            $this->object_router->getPortalPath($chat),
            Breadcrumbs::CHAT_VIEW,
            ['phrase' => 'portal.general.nav-chatlog']
        );

        return $this;
    }

    //####################################################################################################################
    // KB
    //####################################################################################################################

    public function addKb()
    {
        $this->b->add(
            $this->url_generator->generate('portal_kb'),
            Breadcrumbs::KB,
            ['phrase' => 'portal.general.nav-kb']
        );

        return $this;
    }

    public function addKbCat(ArticleCategory $cat)
    {
        $this->b->add(
            $this->object_router->getPortalPath($cat),
            Breadcrumbs::KB_CAT,
            ['title' => $this->language_manager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addKbView(Article $a)
    {
        $this->b->add(
            $this->object_router->getPortalPath($a),
            Breadcrumbs::KB_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // News
    //####################################################################################################################

    public function addNews()
    {
        $this->b->add(
            $this->url_generator->generate('portal_news'),
            Breadcrumbs::NEWS,
            ['phrase' => 'portal.general.nav-news']
        );

        return $this;
    }

    public function addNewsCat(NewsCategory $cat)
    {
        $this->b->add(
            $this->object_router->getPortalPath($cat),
            Breadcrumbs::NEWS_CAT,
            ['title' => $this->language_manager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addNewsView(News $a)
    {
        $this->b->add(
            $this->object_router->getPortalPath($a),
            Breadcrumbs::NEWS_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Downloads
    //####################################################################################################################

    public function addDownloads()
    {
        $this->b->add(
            $this->url_generator->generate('portal_downloads'),
            Breadcrumbs::DOWNLOADS,
            ['phrase' => 'portal.general.nav-downloads']
        );

        return $this;
    }

    public function addDownloadCat(DownloadCategory $cat)
    {
        $this->b->add(
            $this->object_router->getPortalPath($cat),
            Breadcrumbs::DOWNLOADS_CAT,
            ['title' => $this->language_manager->objectPhrase($cat)]
        );

        return $this;
    }

    public function addDownloadView(Download $a)
    {
        $this->b->add(
            $this->object_router->getPortalPath($a),
            Breadcrumbs::DOWNLOADS_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Guides
    //####################################################################################################################

    public function addTopics()
    {
        $this->b->add(
            $this->url_generator->generate('portal_guides'),
            Breadcrumbs::TOPICS,
            ['phrase' => 'portal.general.nav-downloads']
        );

        return $this;
    }

    public function addGuide(Guide $guide)
    {
        $this->b->add(
            $this->object_router->getPortalPath($guide),
            Breadcrumbs::GUIDE,
            ['title' => $this->language_manager->objectPhrase($guide)]
        );

        return $this;
    }

    public function addTopic(Topic $topic)
    {
        $this->b->add(
            $this->object_router->getPortalPath($topic),
            Breadcrumbs::TOPIC,
            $topic
        );

        return $this;
    }

    //####################################################################################################################
    // Profile / Registration
    //####################################################################################################################

    public function addYourAccount()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            ['phrase' => 'portal.general.nav-your-account']
        );

        return $this;
    }

    public function addProfile()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            ['phrase' => 'portal.general.nav-profile']
        );

        return $this;
    }

    public function addEditEmails()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile_emails'),
            Breadcrumbs::PROFILE_EMAILS,
            ['phrase' => 'portal.general.nav-emails']
        );

        return $this;
    }

    public function addRegistration()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_registration'),
            Breadcrumbs::REGISTER,
            ['phrase' => 'portal.general.nav-register']
        );

        return $this;
    }

    public function addLogin()
    {
        $this->b->add(
            $this->url_generator->generate('portal_login'),
            Breadcrumbs::LOGIN,
            ['phrase' => 'portal.general.nav-login']
        );

        return $this;
    }

    public function addPasswordReset()
    {
        $this->b->add(
            $this->url_generator->generate('portal_reset_password'),
            Breadcrumbs::PASSWORD_RESET,
            ['phrase' => 'portal.general.nav-reset-password']
        );

        return $this;
    }

    public function addPasswordSet()
    {
        $this->b->add(
            $this->url_generator->generate('portal_set_password'),
            Breadcrumbs::PASSWORD_SET,
            ['phrase' => 'portal.general.nav-set-password']
        );

        return $this;
    }

    //####################################################################################################################
    // Search
    //####################################################################################################################

    public function addSearch($query)
    {
        $this->b->add(
            $this->url_generator->generate('portal_search', ['q' => $query]),
            Breadcrumbs::SEARCH,
            ['phrase' => 'portal.general.search-section-title']
        );

        $this->b->add(
            $this->url_generator->generate('portal_search', ['q' => $query]),
            Breadcrumbs::SEARCH,
            ['name' => sprintf('"%s"', $query)]
        );

        return $this;
    }

    public function addLabelSearch($type, $label)
    {
        $this->b->add(
            $this->url_generator->generate('portal_search_labels', ['type' => $type, 'label' => $label]),
            Breadcrumbs::SEARCH,
            ['phrase' => 'portal.general.search-labels-section-title']
        );

        if ($label) {
            $this->b->add(
                $this->url_generator->generate('portal_search_labels', ['type' => $type, 'label' => $label]),
                Breadcrumbs::SEARCH,
                ['name' => sprintf('"%s"', $label)]
            )
            ;
        }

        return $this;
    }

    //####################################################################################################################
    // Feedback
    //####################################################################################################################

    public function addFeedback()
    {
        $this->b->add(
            $this->url_generator->generate('portal_feedback'),
            Breadcrumbs::FEEDBACK,
            ['phrase' => 'portal.general.nav-feedback']
        );

        return $this;
    }

    public function addFeedbackView(Feedback $a)
    {
        $this->b->add(
            $this->object_router->getPortalPath($a),
            Breadcrumbs::FEEDBACK_VIEW,
            $a
        );

        return $this;
    }

    //####################################################################################################################
    // Tickets
    //####################################################################################################################

    public function addNewTicket()
    {
        $this->b->add(
            $this->url_generator->generate('portal_new_ticket'),
            Breadcrumbs::TICKETS_NEW,
            ['phrase' => 'portal.general.nav-newticket']
        );

        return $this;
    }

    public function addTicketList()
    {
        $this->b->add(
            $this->url_generator->generate('portal_tickets'),
            Breadcrumbs::TICKETS,
            ['phrase' => 'portal.general.nav-tickets']
        );

        return $this;
    }

    public function addTicketView(Ticket $t)
    {
        $this->b->add(
            $this->object_router->getPortalPath($t),
            Breadcrumbs::TICKETS_VIEW,
            $t
        );

        return $this;
    }

    /**
     * @return Breadcrumbs
     */
    public function done()
    {
        return $this->b;
    }
}
