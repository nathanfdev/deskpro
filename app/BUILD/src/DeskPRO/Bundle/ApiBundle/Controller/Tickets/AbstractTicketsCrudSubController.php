<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\Traits\TicketSearchTrait;

/**
 * Class AbstractTicketsCrudSubController.
 */
abstract class AbstractTicketsCrudSubController extends CrudSubController
{
    use TicketSearchTrait;
}
