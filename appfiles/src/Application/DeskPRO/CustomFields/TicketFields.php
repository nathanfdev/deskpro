<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Symfony\Component\DependencyInjection\ContainerAware;

class TicketFields extends AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefTicket';
	const ENTITY_NAME  = 'DeskPRO:CustomDefTicket';
}