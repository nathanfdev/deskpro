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
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterPreferenceType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/new/ticket_filters/{parentId}/prefs")
 * @ApiDoc(target="all", section="Ticket filter preferences", output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference")
 */
class TicketFilterPrefsController extends CrudSubController
{
    public static $entity         = TicketFilterPreference::class;
    public static $type           = TicketFilterPreferenceType::class;
    public static $parentProperty = 'filter';

    /**
     * @ApiDoc(
     *      description="Set current user preferences for filter",
     *      requirements={
     *          {
     *              "name"="parentId",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *     },
     *      statusCodes={
     *          200="Success",
     *          404="Returned if set was not found"
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function setPrefAction(Request $request)
    {
        $filter = $this->findParentOr404();
        $pref   = $this->getManager()->getRepository(TicketFilterPreference::class)
            ->findOneBy(['filter' => $filter, 'agent' => $this->getUser()]);
        if ($pref) {
            return $this->putAction($pref->getId(), $request);
        }

        return $this->postAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge(
            $options,
            ['filter' => $this->findParentOr404(), 'agent' => $this->getUser()]
        );

        return parent::handleForm($model, $request, $options);
    }
}
