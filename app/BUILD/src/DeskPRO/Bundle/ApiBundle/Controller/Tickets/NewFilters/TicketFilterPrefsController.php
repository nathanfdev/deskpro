<?php

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
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterPreferenceType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference",
 *          "filter"="DeskPRO\Bundle\AppBundle\Entity\TicketFilter",
 *          "agent"="Application\DeskPRO\Entity\Person",
 *      }
 *     }
 * )
 */
class TicketFilterPrefsController extends CrudSubController
{
    public static $entity         = TicketFilterPreference::class;
    public static $type           = TicketFilterPreferenceType::class;
    public static $parentProperty = 'filter';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'filter' => $this->findParentOr404(),
            'agent'  => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
