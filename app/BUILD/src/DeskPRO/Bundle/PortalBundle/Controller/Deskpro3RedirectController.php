<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class Deskpro3RedirectController extends AbstractController
{
    //###########################################################################
    // downloads
    //###########################################################################

    /**
     * files.php
     * files.php?id=123.
     */
    public function downloadCatAction()
    {
        return $this->redirectToRoute('portal_downloads', [], 301);
    }

    /**
     * attachment_files.php?id=123.
     */
    public function downloadViewAction()
    {
        return $this->redirectToRoute('portal_downloads', [], 301);
    }

    //###########################################################################
    // Community
    //###########################################################################

    /**
     * ideas.php
     * ideas.php?cat=123
     * ideas.php?123-some-idea.
     */
    public function communityAction()
    {
        return $this->redirectToRoute('portal_community', [], 301);
    }

    //###########################################################################
    // Articles
    //###########################################################################

    /**
     * kb_article.php?ref=1790-TMRE-3093.
     */
    public function articleViewAction()
    {
        return $this->redirectToRoute('portal_kb', [], 301);
    }

    /**
     * kb_cat.php?id=1.
     */
    public function articleCatAction()
    {
        return $this->redirectToRoute('portal_kb', [], 301);
    }

    /**
     * kb.php.
     */
    public function articlesHomeAction()
    {
        return $this->redirectToRoute('portal_kb', [], 301);
    }

    //###########################################################################
    // News
    //###########################################################################

    /**
     * news.php?id=2
     * news_full.php?id=2.
     */
    public function newsViewAction()
    {
        return $this->redirectToRoute('portal_news', [], 301);
    }

    /**
     * news_archive.php.
     */
    public function newsArchiveAction()
    {
        return $this->redirectToRoute('portal_news', [], 301);
    }

    //###########################################################################
    // Tickets
    //###########################################################################

    /**
     * newticket.php.
     */
    public function newTicketAction()
    {
        return $this->redirectToRoute('portal_new_ticket', [], 301);
    }

    /**
     * ticketlist.php
     * ticketlist_company.php
     * ticketlist_participate.php.
     */
    public function ticketListAction()
    {
        return $this->redirectToRoute('portal_tickets', [], 301);
    }

    /**
     * view.php?ticketref=6630-QVNM-6486.
     */
    public function ticketViewAction()
    {
        return $this->redirectToRoute('portal_tickets', [], 301);
    }

    //###########################################################################
    // Login, reg and profiles
    //###########################################################################

    /**
     * login.php.
     */
    public function loginAction()
    {
        return $this->redirectToRoute('portal_login', [], 301);
    }

    /**
     * register.php.
     */
    public function registerAction()
    {
        return $this->redirectToRoute('portal_user_registration', [], 301);
    }

    /**
     * profile_email.php
     * profile_password.php
     * profile.php.
     */
    public function profileAction()
    {
        return $this->redirectToRoute('portal_user_profile', [], 301);
    }

    //###########################################################################

    /**
     * @param string $lookupId
     *
     * @return int
     */
    public function getNewId($lookupId)
    {
        return null;
    }
}
