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

class BreadcrumbGenerator
{
    /**
     * @return BreadcrumbBuilder
     */
    public function createBuilder()
    {
        return new BreadcrumbBuilder();
    }

    #####################################################################################################################
    # KB
    #####################################################################################################################

    public function buildKb()
    {
        return $this->createBuilder()->addKb()->done();
    }

    public function buildKbCategory(ArticleCategory $cat)
    {
        return $this->createKbCategoryBuilder($cat)->done();
    }

    protected function createKbCategoryBuilder(ArticleCategory $cat)
    {
        $b = $this->createBuilder()->addKb();

        foreach ($cat->getTreeParents() as $c) {
            $b->addKbCat($c);
        }

        $b->addKbCat($cat);

        return $b;
    }

    public function buildKbArticle(Article $a)
    {
        return $this->createKbCategoryBuilder($a->getPrimaryCategory())
            ->addKbView($a)
            ->done();
    }

    #####################################################################################################################
    # News
    #####################################################################################################################

    public function buildNews()
    {
        return $this->createBuilder()->addNews()->done();
    }

    public function buildNewsCategory(NewsCategory $cat)
    {
        return $this->createNewsCategoryBuilder($cat)->done();
    }

    protected function createNewsCategoryBuilder(NewsCategory $cat)
    {
        $b = $this->createBuilder()->addNews();

        foreach ($cat->getTreeParents() as $c) {
            $b->addKbCat($c);
        }

        $b->addNewsCat($cat);

        return $b;
    }

    public function buildNewsPost(News $a)
    {
        return $this->createNewsCategoryBuilder($a->getCategory())
            ->addNewsView($a)
            ->done();
    }

    #####################################################################################################################
    # Downloads
    #####################################################################################################################

    public function buildDownloads()
    {
        return $this->createBuilder()->addDownloads()->done();
    }

    public function buildDownloadsCategory(DownloadCategory $cat)
    {
        return $this->createDownloadsCategoryBuilder($cat)->done();
    }

    protected function createDownloadsCategoryBuilder(DownloadCategory $cat)
    {
        $b = $this->createBuilder()->addDownloads();

        foreach ($cat->getTreeParents() as $c) {
            $b->addDownloadCat($c);
        }

        $b->addDownloadCat($cat);

        return $b;
    }

    public function buildDownloadsFile(Download $a)
    {
        return $this->createDownloadsCategoryBuilder($a->getCategory())
            ->addDownloadView($a)
            ->done();
    }

    #####################################################################################################################
    # Feedback
    #####################################################################################################################

    public function buildFeedback()
    {
        return $this->createBuilder()->addFeedback()->done();
    }

    public function buildFeedbackView(Feedback $a)
    {
        return $this->createBuilder()->addFeedback()
            ->addFeedbackView($a)
            ->done();
    }

    #####################################################################################################################
    # Tickets
    #####################################################################################################################

    public function buildNewTicket()
    {
        return $this->createBuilder()->addNewTicket()->done();
    }

    public function buildTicketList()
    {
        return $this->createBuilder()->addTicketList()->done();
    }

    public function buildTicketView(Ticket $t)
    {
        return $this->createBuilder()->addTicketList()
            ->addTicketView($t)
            ->done();
    }

    public function buildTicketEdit(Ticket $t)
    {
        return $this->buildTicketView($t);
    }

    #####################################################################################################################
    # Profile
    #####################################################################################################################

    public function buildRegistration()
    {
        return $this->createBuilder()->addRegistration()->done();
    }

    public function buildProfile()
    {
        return $this->createBuilder()->addProfile()->done();
    }

    public function buildLogin()
    {
        return $this->createBuilder()->addLogin()->done();
    }

    public function buildPasswordReset()
    {
        return $this->createBuilder()->addPasswordReset()->done();
    }
}