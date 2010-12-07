<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

abstract class AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefAbstract';
	const ENTITY_NAME  = 'DeskPRO:CustomDefAbstract';

	/**
	 * Get a collection of all defined person fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = App::getOrm()->getRepository(static::ENTITY_NAME)->getFields();

		return $fields;
	}

	public function getEnabledFields()
	{
		//TODO

		return $this->getFields();
	}
}