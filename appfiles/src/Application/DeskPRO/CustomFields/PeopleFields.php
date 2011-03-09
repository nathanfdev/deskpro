<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketFilter;
use \Symfony\Component\DependencyInjection\ContainerAware;

class PeopleFields extends AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefPerson';
	const ENTITY_NAME  = 'DeskPRO:CustomDefPerson';
}