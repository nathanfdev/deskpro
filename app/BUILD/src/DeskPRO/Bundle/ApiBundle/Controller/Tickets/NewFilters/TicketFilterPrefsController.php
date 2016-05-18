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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterPreferenceType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_filters/{parentId}/prefs")
 * @ApiDoc(target="all", section="Ticket filter preferences", output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference")
 */
class TicketFilterPrefsController extends CrudSubController
{
    public static $entity         = TicketFilterPreference::class;
    public static $type           = TicketFilterPreferenceType::class;
    public static $parentProperty = 'filter';

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var TicketFilter $parent */
        $parent = $this->findParentOr404();
        $agent  = $this->getUser();
        /** @var TicketFilterPreference $entity */
        $entity = $this->getManager()->getRepository(TicketFilterPreference::class)
            ->findOneBy(['filter' => $parent, 'agent' => $agent]);

        return $entity;
    }
}
