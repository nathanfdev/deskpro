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

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Ticket;
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
            array('phrase' => 'portal.general.nav-portal')
        );
        $this->language_manager = $language_manager;
    }

    #####################################################################################################################
    # CHAT
    #####################################################################################################################

    public function addChat()
    {
        $this->b->add(
            $this->url_generator->generate('portal_chats'),
            Breadcrumbs::CHAT,
            array('phrase' => 'portal.general.nav-chat')
        );

        return $this;
    }

    public function addChatView(ChatConversation $chat)
    {
        $this->b->add(
            $this->object_router->getPortalPath($chat),
            Breadcrumbs::CHAT_VIEW,
            array('phrase' => 'portal.general.nav-chatlog')
        );

        return $this;
    }

    #####################################################################################################################
    # KB
    #####################################################################################################################

    public function addKb()
    {
        $this->b->add(
            $this->url_generator->generate('portal_kb'),
            Breadcrumbs::KB,
            array('phrase' => 'portal.general.nav-kb')
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

    #####################################################################################################################
    # News
    #####################################################################################################################

    public function addNews()
    {
        $this->b->add(
            $this->url_generator->generate('portal_news'),
            Breadcrumbs::NEWS,
            array('phrase' => 'portal.general.nav-news')
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

    #####################################################################################################################
    # Downloads
    #####################################################################################################################

    public function addDownloads()
    {
        $this->b->add(
            $this->url_generator->generate('portal_downloads'),
            Breadcrumbs::DOWNLOADS,
            array('phrase' => 'portal.general.nav-downloads')
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

    #####################################################################################################################
    # Profile / Registration
    #####################################################################################################################

    public function addYourAccount()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            array('phrase' => 'portal.general.nav-your-account')
        );

        return $this;
    }

    public function addProfile()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile'),
            Breadcrumbs::PROFILE,
            array('phrase' => 'portal.general.nav-profile')
        );

        return $this;
    }

    public function addEditEmails()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_profile_emails'),
            Breadcrumbs::PROFILE_EMAILS,
            array('phrase' => 'portal.general.nav-emails')
        );

        return $this;
    }

    public function addRegistration()
    {
        $this->b->add(
            $this->url_generator->generate('portal_user_registration'),
            Breadcrumbs::REGISTER,
            array('phrase' => 'portal.general.nav-register')
        );

        return $this;
    }

    public function addLogin()
    {
        $this->b->add(
            $this->url_generator->generate('portal_login'),
            Breadcrumbs::LOGIN,
            array('phrase' => 'portal.general.nav-login')
        );

        return $this;
    }

    public function addPasswordReset()
    {
        $this->b->add(
            $this->url_generator->generate('portal_reset_password'),
            Breadcrumbs::PASSWORD_RESET,
            array('phrase' => 'portal.general.nav-reset-password')
        );

        return $this;
    }

    public function addPasswordSet()
    {
        $this->b->add(
            $this->url_generator->generate('portal_set_password'),
            Breadcrumbs::PASSWORD_SET,
            array('phrase' => 'portal.general.nav-set-password')
        );

        return $this;
    }

    #####################################################################################################################
    # Search
    #####################################################################################################################

    public function addSearch($query)
    {
        $this->b->add(
            $this->url_generator->generate('portal_search', array('q' => $query)),
            Breadcrumbs::SEARCH,
            array('phrase' => 'portal.general.search-section-title')
        );

        $this->b->add(
            $this->url_generator->generate('portal_search', array('q' => $query)),
            Breadcrumbs::SEARCH,
            array('name' => sprintf('"%s"', $query))
        );

        return $this;
    }

    public function addLabelSearch($type, $label)
    {
        $this->b->add(
            $this->url_generator->generate('portal_search_labels', array('type' => $type, 'label' => $label)),
            Breadcrumbs::SEARCH,
            array('phrase' => 'portal.general.search-labels-section-title')
        );

        if ($label) {
            $this->b->add(
                $this->url_generator->generate('portal_search_labels', array('type' => $type, 'label' => $label)),
                Breadcrumbs::SEARCH,
                array('name' => sprintf('"%s"', $label))
            )
            ;
        }

        return $this;
    }

    #####################################################################################################################
    # Feedback
    #####################################################################################################################

    public function addFeedback()
    {
        $this->b->add(
            $this->url_generator->generate('portal_feedback'),
            Breadcrumbs::FEEDBACK,
            array('phrase' => 'portal.general.nav-feedback')
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

    #####################################################################################################################
    # Tickets
    #####################################################################################################################

    public function addNewTicket()
    {
        $this->b->add(
            $this->url_generator->generate('portal_new_ticket'),
            Breadcrumbs::TICKETS_NEW,
            array('phrase' => 'portal.general.nav-newticket')
        );

        return $this;
    }

    public function addTicketList()
    {
        $this->b->add(
            $this->url_generator->generate('portal_tickets'),
            Breadcrumbs::TICKETS,
            array('phrase' => 'portal.general.nav-tickets')
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
