<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldOptionsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketCustomFieldOptionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_custom_fields/{parentId}/options")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\CustomDefTicket")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldOptionType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefTicket",
 *          "parent"="Application\DeskPRO\Entity\CustomDefTicket"
 *      }
 *     }
 * )
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\CustomDefAbstract": "DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefChoice"
 * })
 */
class TicketCustomFieldOptionsController extends AbstractCustomFieldOptionsController
{
    public static $entity = CustomDefTicket::class;
}
