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
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getItemsPager($page, $max_per_page)
    {
        // TODO: this needs to be a full blown search with filters
        $items = new ArrayCollection($this->getItemsRepo()->findAll());

        // TODO: make sure this collection adapter gets a collection that is EXTRA_LAZY!
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($items));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param int|null|Feedback $item
     * @return null|Feedback
     */
    public function getItem($item)
    {
        if (!$item) { // we need some input
            return null;
        }

        if ($item instanceof Feedback) { // already have what you seek
            return $item;
        }

        return $this->getItemsRepo()->find($item);
    }

    public function getItemComments($item, Person $person = null)
    {
        $item = $this->getItem($item);

        return $this->getItemCommetRepo()->getDisplayComments($item, $person);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Feedback
     */
    public function getItemsRepo()
    {
        return $this->em->getRepository('DeskPRO:Feedback');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\FeedbackComment
     */
    public function getItemCommetRepo()
    {
        return $this->em->getRepository('DeskPRO:FeedbackComment');
    }
}
 