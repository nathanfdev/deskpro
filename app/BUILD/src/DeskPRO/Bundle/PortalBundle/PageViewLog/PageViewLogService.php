<?php

/**
 * Created by PhpStorm.
 * User: yakut
 * Date: 16.03.17
 * Time: 16:32.
 */

namespace DeskPRO\Bundle\PortalBundle\PageViewLog;

use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class PageViewLogService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function pageView(Person $person, $type, $id, $action = PageViewLog::ACTION_VIEW)
    {
        $pageView = new PageViewLog();
        $pageView
            ->setObjectType($type)
            ->setPerson($person)
            ->setObjectId($id)
            ->setActionView($action);

        $this->em->persist($pageView);
        $this->em->flush();
    }
}
