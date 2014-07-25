<?php

namespace Application\DeskPRO\Log\Event;


use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\PersonContactData;
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
		$ret = array(
			'property' => $change->getField(),
			'old' => null,
			'new' => null,
		);

		switch (true) {

			case ($change instanceof ChangeSimple):
				$ret['old'] = $old;
				$ret['new'] = $new;
				break;


			case ($change instanceof ChangeObject):

				if ($old instanceof PersonEmail) {
					$ret['old'] = $old['email'];
				}

				if ($new instanceof PersonEmail) {
					$ret['new'] = $new['email'];
				}

				break;


			case ($change instanceof ChangeCollection):

				$ret['add'] = array_map(array($this, 'mapObject'), $change->getAddedElements());
				$ret['del'] = array_map(array($this, 'mapObject'), $change->getRemovedElements());

				break;
		}

		return $ret;
	}

	/**
	 * todo stringify handler
	 * @param DomainObject $obj
	 * @return string
	 */
	public function mapObject(DomainObject $obj)
	{
		switch (true) {
			case ($obj instanceof PersonContactData):
				return $obj['contact_type'] . ' ' . $obj['field_10'];
				break;

			case ($obj instanceof LabelPerson):
				return 'label ' . $obj['label'];
				break;
		}
	}
} 