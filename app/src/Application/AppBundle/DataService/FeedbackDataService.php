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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\DataService;


use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param FeedbackCategory $category
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getFeedbackPager(FeedbackCategory $category, $page, $max_per_page)
    {
        // TODO: make sure this collection adapter gets a collection that is EXTRA_LAZY!
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($category->feedbacks));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
 