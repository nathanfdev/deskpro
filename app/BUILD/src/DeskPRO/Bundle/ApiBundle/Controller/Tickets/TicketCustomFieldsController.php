<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class TicketCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_custom_fields")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\CustomDefTicket")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefTicket"
 *      }
 *     }
 * )
 */
class TicketCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefTicket::class;
}
