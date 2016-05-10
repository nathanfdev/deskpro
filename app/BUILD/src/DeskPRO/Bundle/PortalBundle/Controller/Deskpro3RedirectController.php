<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class Deskpro3RedirectController extends AbstractController
{
    ############################################################################
    # downloads
    ############################################################################

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

    ############################################################################
    # Feedback
    ############################################################################

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

    ############################################################################
    # Articles
    ############################################################################

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

    ############################################################################
    # News
    ############################################################################

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

    ############################################################################
    # Tickets
    ############################################################################

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

    ############################################################################
    # Login, reg and profiles
    ############################################################################

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

    ############################################################################
    # Unsupported : Manuals and Troubles
    ############################################################################

    public function rewrittenManualsAction($manualBit = '', $pageBit = '')
    {
        $manulaId = Strings::extractRegexMatch('#^(\d+)#', $manualBit);
        $pageId   = Strings::extractRegexMatch('#^(\d+)#', $pageBit);

        if (!$manulaId && !$pageId) {
            return $this->redirectToRoute('user', [], 301);
        }

        if ($pageId) {
            return $this->redirectToRoute('dp3_redirect_manual_php', ['m' => $manulaId, 'p' => $pageId]);
        } else {
            return $this->redirectToRoute('dp3_redirect_manual_php', ['m' => $manulaId]);
        }
    }

    /**
     * manual.php
     * manual.php?m=2
     * manual.php?m=2
     * manual.php?p=49
     * manual_download.php?m=2&do=single
     * manual_download.php?m=2&do=zip.
     */
    public function manualsAction()
    {
        return $this->redirectToRoute('user', [], 301);
    }

    /**
     * troubleshooter.php
     * troubleshooter.php?id=1.
     */
    public function troublesAction()
    {
        return $this->redirectToRoute('user', [], 301);
    }

    ############################################################################

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
