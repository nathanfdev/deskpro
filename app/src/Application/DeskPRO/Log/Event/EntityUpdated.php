<?php

namespace Application\DeskPRO\Log\Event;


use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\ORM\StateChange\ChangeArray;
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

	// todo hardcoded render
	protected $renderedCustomFields;

	public function __construct(DomainObject $entity, ChangeInterface $change = null)
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
		if (!$change = $this->change) {
			return array();
		}

		$old = $this->change->getOld();
		$new = $this->change->getNew();
		$ret = array(
			'property' => $change->getField(),
			'old' => null,
			'new' => null,
			'add' => array(),
			'del' => array(),
		);

		switch (true) {

			case ($change instanceof ChangeSimple || $change instanceof ChangeArray):
				$ret['old'] = $old;
				$ret['new'] = $new;
				break;


			case ($change instanceof ChangeObject):

				$ret['old'] = $this->mapObject($old);
				$ret['new'] = $this->mapObject($new);

				break;


			case ($change instanceof ChangeCollection):

				$ret['add'] = array_map(array($this, 'mapObject'), $change->getAddedElements());
				$ret['del'] = array_map(array($this, 'mapObject'), $change->getRemovedElements());

				break;
		}

		return $ret;
	}

	/**
	 * todo should be processed in separate handler for each sort of subject
	 * @param DomainObject $obj
	 * @return string
	 */
	public function mapObject(DomainObject $obj = null)
	{
		switch (true) {


			case ($obj instanceof PersonContactData):
				return array_merge(
					array('contact_type' => $obj['contact_type']),
					$obj->getHandler()->getApiVars($obj)
				);
				break;


			case ($obj instanceof LabelPerson):
				return $obj['label'];
				break;


			case ($obj instanceof PersonEmail):
				return $obj['email'];
				break;


			case ($obj instanceof Blob):
				return $obj['id'];
				break;


			case ($obj instanceof Organization):
				return $obj['name'];
				break;


			case ($obj instanceof Usergroup):
				return $obj['title'];
				break;
		}
	}
} 