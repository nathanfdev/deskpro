<?php

namespace Application\DeskPRO\Log\Event;


use Application\DeskPRO\Domain\DomainObject;

abstract class Base
{
	/**
	 * event name
	 * @return string
	 */
	abstract public function getName();

	/**
	 * details about this event
	 * @return array
	 */
	abstract public function getDetails();

	/**
	 * affected subject
	 * @return DomainObject|null
	 */
	abstract public function getSubject();
} 