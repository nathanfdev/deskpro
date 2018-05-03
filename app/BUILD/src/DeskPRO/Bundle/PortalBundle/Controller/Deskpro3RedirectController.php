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
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_file_cat_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:DownloadCategory', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_downloads_view', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

        return $this->redirectToRoute('portal_downloads', [], 301);
    }

    /**
     * attachment_files.php?id=123.
     */
    public function downloadViewAction()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_filescat_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:Download', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_downloads_view', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

        return $this->redirectToRoute('portal_downloads', [], 301);
    }

    //###########################################################################
    // Feedback
    //###########################################################################

    /**
     * ideas.php
     * ideas.php?cat=123
     * ideas.php?123-some-idea.
     */
    public function feedbackAction()
    {
        $catId   = isset($_GET['cat']) ? $_GET['cat'] : 0;
        $ideaStr = Arrays::getFirstKey($_GET);

        if ($catId) {
            // Ignore (go to home)
            // We dont filter on cats anymore
        } elseif ($ideaStr) {
            $id    = Strings::extractRegexMatch('#^([0-9]+)#', $ideaStr);
            $newId = $this->getNewId('dp3_ideaid_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:Feedback', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_feedback_view', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

        return $this->redirectToRoute('portal_feedback', [], 301);
    }

    //###########################################################################
    // Articles
    //###########################################################################

    /**
     * kb_article.php?ref=1790-TMRE-3093.
     */
    public function articleViewAction()
    {
        $id = isset($_GET['ref']) ? $_GET['ref'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_kbref_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:Article', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_kb_view', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

        return $this->redirectToRoute('portal_kb', [], 301);
    }

    /**
     * kb_cat.php?id=1.
     */
    public function articleCatAction()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_kbcatid_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:ArticleCategory', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_kb_browse', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

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
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_newsid_'.$id);
            if ($newId) {
                $obj = $this->getEm()->find('DeskPRO:News', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_news_view', ['slug' => $obj->getUrlSlug()], 301);
                }
            }
        }

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
        $id = isset($_GET['ticketref']) ? $_GET['ticketref'] : 0;

        if ($id) {
            $newId = $this->getNewId('dp3_ticketref_'.$id);
            if ($newId) {
                $newId = $newId['new_id'];
                $obj   = $this->getEm()->find('DeskPRO:Ticket', $newId);
                if ($obj) {
                    return $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $obj->getRef()], 301);
                }
            }
        }

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
        $data = $this->getDb()->fetchColumn('SELECT data FROM import_datastore WHERE typename = ?', [$lookupId]);

        if (preg_match('#^a:[0-9]+:\{#', $data)) {
            $data = unserialize($data);
        }

        return $data;
    }
}
