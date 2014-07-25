<?php

namespace Application\DeskPRO\Log\Event;


use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\PersonEmail;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\ChangeObject;
use Application\DeskPRO\ORM\StateChange\ChangeSimple;

class EntityUpdated extends Base
{
	/** @var \Application\DeskPRO\Domain\DomainObject  */
	protected $entity;

	/** @var \Application\DeskPRO\ORM\StateChange\ChangeInterface  */
	protected $change;

	public function __construct(DomainObject $entity, ChangeInterface $change)
	{
		$this->entity = $entity;
		$this->change = $change;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'entity_updated';
	}

	/**
	 * @inheritdoc
	 */
	public function getSubject()
	{
		return $this->entity;
	}

	/**
	 * @inheritdoc
	 */
	public function getDetails()
	{
		$change = $this->change;
		$old = $this->change->getOld();
		$new = $this->change->getNew();

		switch (true) {

			case ($change instanceof ChangeSimple):
				// do nothing
				break;


			case ($change instanceof ChangeObject):

				if ($old instanceof PersonEmail) {
					$old = $old['email'];
				}

				if ($new instanceof PersonEmail) {
					$new = $new['email'];
				}

				break;


			case ($change instanceof ChangeCollection):
				break;
		}

		return array(
			'property' => $change->getField(),
			'old' => $old,
			'new' => $new,
		);
	}
} 