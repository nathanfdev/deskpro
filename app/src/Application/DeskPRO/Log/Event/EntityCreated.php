<?php

namespace Application\DeskPRO\Log\Event;


use Application\DeskPRO\Domain\DomainObject;

class EntityCreated extends Base
{
	/** @var DomainObject */
	protected $entity;

	public function __construct(DomainObject $entity)
	{
		$this->entity = $entity;
	}

	public function getName()
	{
		return 'entity_created';
	}

	public function getDetails()
	{
		return array();
	}

	public function getSubject()
	{
		return $this->entity;
	}
} 