<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Ticket;

class BreadcrumbBuilder
{
    /**
     * @var Breadcrumbs
     */
    private $b;

    public function __construct()
    {
        $this->b = new Breadcrumbs();
        $this->b->add(
            'portal_index',
            null,
            Breadcrumbs::PORTAL,
            array('phrase' => 'portal.general.nav-portal')
        );
    }

    #####################################################################################################################
    # KB
    #####################################################################################################################

    public function addKb()
    {
        $this->b->add(
            'portal_kb',
            null,
            Breadcrumbs::KB,
            array('phrase' => 'portal.general.nav-kb')
        );
        return $this;
    }

    public function addKbCat(ArticleCategory $cat)
    {
        $this->b->add(
            'portal_kb_browse',
            array('slug' => $cat->getSlug()),
            Breadcrumbs::KB_CAT,
            $cat
        );

        return $this;
    }

    public function addKbView(Article $a)
    {
        $this->b->add(
            'portal_kb_view',
            array('slug' => $a->getSlug()),
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
            'portal_news',
            null,
            Breadcrumbs::NEWS,
            array('phrase' => 'portal.general.nav-news')
        );
        return $this;
    }

    public function addNewsCat(NewsCategory $cat)
    {
        $this->b->add(
            'portal_news_browse',
            array('slug' => $cat->getSlug()),
            Breadcrumbs::NEWS_CAT,
            $cat
        );

        return $this;
    }

    public function addNewsView(News $a)
    {
        $this->b->add(
            'portal_news_view',
            array('slug' => $a->getSlug()),
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
            'portal_downloads',
            null,
            Breadcrumbs::DOWNLOADS,
            array('phrase' => 'portal.general.nav-downloads')
        );
        return $this;
    }

    public function addDownloadCat(DownloadCategory $cat)
    {
        $this->b->add(
            'portal_downloads_browse',
            array('slug' => $cat->getSlug()),
            Breadcrumbs::DOWNLOADS_CAT,
            $cat
        );

        return $this;
    }

    public function addDownloadView(Download $a)
    {
        $this->b->add(
            'portal_downloads_view',
            array('slug' => $a->getSlug()),
            Breadcrumbs::DOWNLOADS_VIEW,
            $a
        );

        return $this;
    }

    #####################################################################################################################
    # Profile / Registration
    #####################################################################################################################

    public function addProfile()
    {
        $this->b->add(
            'portal_user_profile',
            null,
            Breadcrumbs::PROFILE,
            array('phrase' => 'portal.general.nav-profile')
        );
        return $this;
    }

    public function addRegistration()
    {
        $this->b->add(
            'portal_user_registration',
            null,
            Breadcrumbs::REGISTER,
            array('phrase' => 'portal.general.nav-register')
        );

        return $this;
    }

    public function addLogin()
    {
        $this->b->add(
            'portal_login',
            null,
            Breadcrumbs::LOGIN,
            array('phrase' => 'portal.general.nav-login')
        );

        return $this;
    }

    public function addPasswordReset()
    {
        $this->b->add(
            'portal_reset_password',
            null,
            Breadcrumbs::PASSWORD_RESET,
            array('phrase' => 'portal.general.nav-reset-password')
        );

        return $this;
    }

    #####################################################################################################################
    # Feedback
    #####################################################################################################################

    public function addFeedback()
    {
        $this->b->add(
            'portal_feedback',
            null,
            Breadcrumbs::FEEDBACK,
            array('phrase' => 'portal.general.nav-feedback')
        );

        return $this;
    }
    public function addFeedbackView(Feedback $a)
    {
        $this->b->add(
            'portal_feedback_view',
            array('slug' => $a->getSlug()),
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
            'portal_new_ticket',
            null,
            Breadcrumbs::TICKETS_NEW,
            array('phrase' => 'portal.general.nav-newticket')
        );

        return $this;
    }

    public function addTicketList()
    {
        $this->b->add(
            'portal_tickets',
            null,
            Breadcrumbs::TICKETS,
            array('phrase' => 'portal.general.nav-tickets')
        );

        return $this;
    }

    public function addTicketView(Ticket $t)
    {
        $this->b->add(
            'portal_tickets_view',
            array('id' => $t->getId()),
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