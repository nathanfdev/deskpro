<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\AuditLog;
use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\AuditLog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class AuditDoctrineListener implements \Doctrine\Common\EventSubscriber
{
	/**
	 * @var AuditManager
	 */
	protected $audit_manager;

	/**
	 * @var array
	 */
	protected $defs = array();

	/**
	 * @var array
	 */
	protected $new_entities = array();

	/**
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * @var callback
	 */
	protected $filter_fn;


	/**
	 * @param AuditManager $audit_manager
	 * @param array        $defs
	 * @param callback     $filter_fn
	 */
	public function __construct(AuditManager $audit_manager, array $defs, $filter_fn = null)
	{
		$this->audit_manager = $audit_manager;
		$this->defs = $defs;
		$this->filter_fn = $filter_fn;
	}

	/**
	 * Disable the audit manager
	 */
	public function disable()
	{
		$this->disabled = true;
	}


	/**
	 * Enable the audit manager
	 */
	public function enable()
	{
		$this->disabled = false;
	}


	/**
	 * Check if the audit manager is enabled
	 */
	public function isEnabled()
	{
		return !$this->disabled;
	}


	/**
	 * @param callable $filter_fn
	 */
	public function setFilterFn($filter_fn)
	{
		$this->filter_fn = $filter_fn;
	}


	/**
	 * @param PreUpdateEventArgs $eventArgs
	 */
	public function preUpdate(PreUpdateEventArgs $eventArgs)
	{
		if ($this->disabled) return;

		$entity = $eventArgs->getEntity();
		if (!($entity instanceof DomainObject)) {
			// Not a valid entity
			return;
		}

		$table = $entity->getTableName();
		if (!isset($this->defs[$table])) {
			// Not a tracked object
			return;
		}

		if ($this->filter_fn) {
			if (!call_user_func($this->filter_fn, 'preUpdate', $table, $eventArgs)) return;
		}

		foreach ($eventArgs->getEntityChangeSet() as $change_field => $change_data) {

			if (!isset($this->defs[$table]['fields']) || !in_array($change_field, $this->defs[$table]['fields'])) {
				continue;
			}

			if (isset($this->defs[$table]['do_log_check'])) {
				$fn = $this->defs[$table]['do_log_check'];
				if (!$fn($entity, AuditLog::UPDATE)) {
					return;
				}
			}


			$old = $change_data[0];
			$new = $change_data[1];

			if ($old == $new) {
				continue;
			}

			if (isset($this->defs[$table]['save_as_change'])) {
				$save_as     = $this->defs[$table]['save_as_change'];

				if (is_string($save_as['object_field_id'])) {
					$save_obj = $entity[$save_as['object_field_id']];
				} else {
					$fn = $save_as['object_field_id'];
					$save_obj = $fn($entity);
				}

				$save_field  = $save_as['as_field_id'];
			} else {
				$save_obj    = $entity;
				$save_field  = $change_field;
			}

			$this->audit_manager->recordChange(
				$save_obj,
				$save_field,
				$this->_formatDataValue($old),
				$this->_formatDataValue($new)
			);
		}
	}


	/**
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function preRemove(LifecycleEventArgs $eventArgs)
	{
		if ($this->disabled) return;

		$entity = $eventArgs->getEntity();
		if (!($entity instanceof DomainObject)) {
			// Not a valid entity
			return;
		}

		$table = $entity->getTableName();
		if (!isset($this->defs[$table])) {
			// Not a tracked object
			return;
		}

		if ($this->filter_fn) {
			if (!call_user_func($this->filter_fn, 'preRemove', $table, $eventArgs)) return;
		}

		if (isset($this->defs[$table]['do_log_check'])) {
			$fn = $this->defs[$table]['do_log_check'];
			if (!$fn($entity, AuditLog::DELETE)) {
				return;
			}
		}

		#------------------------------
		# Save a normal created event
		#------------------------------

		if (!isset($this->defs[$table]['save_as_change'])) {

			$this->audit_manager->recordDelete($entity);

		#------------------------------
		# Saved on a different object as a change
		#------------------------------

		} else {
			$save_as     = $this->defs[$table]['save_as_change'];

			if (is_string($save_as['object_field_id'])) {
				$save_obj = $entity[$save_as['object_field_id']];
			} else {
				$fn = $save_as['object_field_id'];
				$save_obj = $fn($entity);
			}

			$save_field  = $save_as['as_field_id'];
			$save_table  = $save_obj->getTableName();

			if (isset($this->defs[$save_table]['do_log_check'])) {
				$fn = $this->defs[$save_table]['do_log_check'];
				if (!$fn($save_obj, AuditLog::DELETE)) {
					return;
				}
			}

			if (isset($save_as['render_value'])) {
				$fn = $save_as['render_value'];
				$val = $fn($entity, AuditLog::DELETE);
			} else {
				$val = AuditLog::getObjectNameFromVar($entity);
			}

			$this->audit_manager->recordChange($save_obj, $save_field, null, $val);
		}
	}


	/**
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postPersist(LifecycleEventArgs $eventArgs)
	{
		if ($this->disabled) return;

		$entity = $eventArgs->getEntity();
		if (!($entity instanceof DomainObject)) {
			// Not a valid entity
			return;
		}

		$table = $entity->getTableName();
		if (!isset($this->defs[$table])) {
			// Not a tracked object
			return;
		}

		if ($this->filter_fn) {
			if (!call_user_func($this->filter_fn, 'postPersist', $table, $eventArgs)) return;
		}

		if (isset($this->defs[$table]['do_log_check'])) {
			$fn = $this->defs[$table]['do_log_check'];
			if (!$fn($entity, AuditLog::DELETE)) {
				return;
			}
		}

		#------------------------------
		# Save a normal created event
		#------------------------------

		if (!isset($this->defs[$table]['save_as_change'])) {

			$this->audit_manager->recordCreated($entity);

		#------------------------------
		# Saved on a different object as a change
		#------------------------------

		} else {
			$save_as     = $this->defs[$table]['save_as_change'];

			if (is_string($save_as['object_field_id'])) {
				$save_obj = $entity[$save_as['object_field_id']];
			} else {
				$fn = $save_as['object_field_id'];
				$save_obj = $fn($entity);
			}

			$save_field  = $save_as['as_field_id'];
			$save_table  = $save_obj->getTableName();

			if (isset($this->defs[$save_table]['do_log_check'])) {
				$fn = $this->defs[$save_table]['do_log_check'];
				if (!$fn($save_obj, AuditLog::CREATE)) {
					return;
				}
			}

			if (isset($save_as['render_value'])) {
				$fn = $save_as['render_value'];
				$val = $fn($entity, AuditLog::CREATE);
			} else {
				$val = AuditLog::getObjectNameFromVar($entity);
			}

			$this->audit_manager->recordChange($save_obj, $save_field, null, $val);
		}
	}


	/**
	 * Flush logs on commit
	 */
	public function onPostCommit()
	{
		//$this->audit_manager->flushLogs();
	}


	/**
	 * @param $value
	 * @return string
	 */
	private function _formatDataValue($value)
	{
		if ($value instanceof \DateTime) {
			return $value->format('Y-m-d H:i:s');
		} else if ($value instanceof DomainObject) {
			$str = AuditLog::getObjectNameFromVar($value);
			if (method_exists($value, '__toString')) {
				$str .= ' -- ' . $value->__toString();
			}

			return $str;
		} else {
			if (is_string($value) || is_scalar($value) || ctype_digit($value)) {
				return (string)$value;
			} else if (is_array($value)) {
				return print_r($value, true);
			} else if (is_null($value)) {
				return null;
			} else if (is_object($value)) {
				return get_class($value);
			}
		}

		return 'unknown type';
	}


	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::preUpdate,
			Events::preRemove,
			Events::postPersist,
			\Application\DeskPRO\DBAL\Connection::EVENT_POST_COMMIT
		);
	}
}