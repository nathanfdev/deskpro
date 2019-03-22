<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;

class StateChangeRecorder
{
    /**
     * @var int
     */
    private static $global_state_version = 0;

    /**
     * @var int
     */
    private $state_version;

    /**
     * @var array
     */
    private $touched_fields = [];

    /**
     * @var \Application\DeskPRO\ORM\StateChange\ChangeInterface[]
     */
    private $changes = [];

    /**
     * @var array[]
     */
    private $changes_by_field = [];

    /**
     * @var array
     */
    private $change_metadata = [];

    /**
     * Array of object IDs (changes[]) to the state they were at when the change was registered.
     *
     * @var array
     */
    private $changes_to_state_version = [];

    /**
     * @var array
     */
    private $current_change_metadata = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state_version = self::$global_state_version;
    }

    /**
     * @return int
     */
    public static function getGlobalStateVersion()
    {
        return self::$global_state_version;
    }

    /**
     * An incrementing counter that increases every time any change is made
     * to this object. When this state is higher, it means some kind of change
     * was made (eg you can compare it with a previous value to see if any changes were made).
     *
     * This exists for the lifetime of the current request; it is not persisted anywhere.
     *
     * @return int
     */
    public function getStateVersion()
    {
        return $this->state_version;
    }

    /**
     * Given a change, get the state version for that change. Will return 0 if no
     * such change is registered.
     *
     * @param ChangeInterface $change
     *
     * @return int
     */
    public function getStateVersionForChange(ChangeInterface $change)
    {
        $id = spl_object_hash($change);

        return isset($this->changes_to_state_version[$id]) ? $this->changes_to_state_version[$id] : 0;
    }

    /**
     * @param ChangeInterface $change
     */
    private function addChange(ChangeInterface $change)
    {
        $field_id = $change->getField();

        if (!isset($this->changes_by_field[$field_id])) {
            $this->changes_by_field[$field_id] = [];
        }

        $this->changes[]                     = $change;
        $this->changes_by_field[$field_id][] = $change;

        if (!($change instanceof NonStateTrackingInterface)) {
            ++self::$global_state_version;
            ++$this->state_version;
        }

        if ($this->current_change_metadata) {
            $id                         = spl_object_hash($change);
            $this->change_metadata[$id] = $this->current_change_metadata;
        }

        $this->touched_fields[$field_id] = true;

        $this->changes_to_state_version[spl_object_hash($change)] = $this->state_version;
    }

    /**
     * Record a new change.
     *
     * @param string $field_id
     * @param mixed  $old
     * @param mixed  $new
     * @param bool   $skip_same
     *
     * @return ChangeArray|ChangeDate|ChangeObject|ChangeSimple|null
     */
    public function record($field_id, $old, $new, $skip_same = true)
    {
        if ($old === null && $new === null) {
            $use_type = $old;
        } elseif ($old === null) {
            $use_type = $new;
        } else {
            $use_type = $old;
        }

        if (is_object($use_type)) {
            if ($use_type instanceof \DateTime) {
                $change = new ChangeDate($field_id, $use_type);
            } elseif ($use_type instanceof DomainObject) {
                $change = new ChangeObject($field_id, $old, $new);
            } else {
                $change = new ChangeObject($field_id, $old, $new);
            }
        } elseif (is_array($use_type)) {
            $change = new ChangeArray($field_id, $old, $new);
        } else {
            $change = new ChangeSimple($field_id, $old, $new);
        }

        $this->touched_fields[$field_id] = true;

        if ($skip_same && $change->isSame()) {
            return;
        }

        $this->addChange($change);

        return $change;
    }

    /**
     * @param ChangeInterface $change
     */
    public function recordChange(ChangeInterface $change)
    {
        $this->addChange($change);
    }

    /**
     * @param string $field_id
     * @param array  $data
     *
     * @return ChangeDate
     */
    public function recordData($field_id, array $data = [])
    {
        $change = new ChangeData($field_id, $data);
        $this->addChange($change);

        return $change;
    }

    /**
     * @param string     $field_id
     * @param Collection $coll
     * @param bool       $skip_same
     *
     * @return ChangeCollection|null
     */
    public function recordCollection($field_id, Collection $coll, $skip_same = true)
    {
        $old = [];
        if (isset($this->changes_by_field[$field_id]) && ($_coll = end($this->changes_by_field[$field_id]))) {
            $old = $_coll->getNew();
            if (!is_array($old)) {
                $old = [$old];
            }
        } elseif ($coll instanceof PersistentCollection) {
            if (!$coll->isInitialized()) {
                // collection might not be loaded by this point
                // in any case we need to load it to get snaphost and generate changeset
                $coll->initialize();
            }
            $old = $coll->getSnapshot();
        }

        $change = ChangeCollection::newFromPersistedCollection($field_id, $coll, $old);
        if ($skip_same && $change->isSame()) {
            return;
        }

        $this->addChange($change);

        return $change;
    }

    /**
     * @param string $field_id
     *
     * @return bool
     */
    public function hasChangedField($field_id)
    {
        return isset($this->changes_by_field[$field_id]);
    }

    /**
     * @return ChangeInterface[]
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param string $field_id
     *
     * @return ChangeInterface[]
     */
    public function getChangesForField($field_id)
    {
        return isset($this->changes_by_field[$field_id]) ? $this->changes_by_field[$field_id] : [];
    }

    /**
     * For fields that were changed multiple times, this returns
     * a change where the old is the first old, and the new is the last new
     * (eg multiple changes made inbetween are not included).
     *
     * @param string $field_id
     *
     * @return ChangeInterface
     */
    public function getCombinedChangeForField($field_id)
    {
        if (!isset($this->changes_by_field[$field_id])) {
            return;
        }

        $changes = $this->changes_by_field[$field_id];
        $first   = array_shift($changes);
        $last    = $changes ? array_pop($changes) : null;

        // Only the one change, so
        // can just return that
        if ($last === null) {
            return $first;
        }

        $class = get_class($first);

        switch ($class) {
            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeDate':
            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeEntity':
            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeObject':
            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeSimple':
                $change = new $class($first->getOld(), $last->getNew());

                return $change;

            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeData':
                return $first;

            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeArray':
            case 'Application\\DeskPRO\\ORM\\StateChange\\ChangeCollection':
                $change = new $class($first->getOld(), $last->getNew());

                return $change;
        }

        return $first;
    }

    /**
     * @return string[]
     */
    public function getChangedFields()
    {
        return array_keys($this->changes_by_field);
    }

    /**
     * @param $field_id
     *
     * @return ChangeInterface|null
     */
    public function getFirstChangeForField($field_id)
    {
        if (!isset($this->changes_by_field[$field_id])) {
            return;
        }

        return $this->changes_by_field[$field_id][0];
    }

    /**
     * @param $field_id
     *
     * @return ChangeInterface|null
     */
    public function getLastChangeForField($field_id)
    {
        if (!isset($this->changes_by_field[$field_id])) {
            return;
        }

        $idx = count($this->changes_by_field[$field_id]) - 1;

        return $this->changes_by_field[$field_id][$idx];
    }

    /**
     * @param string $field_id
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getOriginalValueForField($field_id)
    {
        $change = $this->getFirstChangeForField($field_id);
        if (!$change) {
            throw new \InvalidArgumentException('Field was not modified, cannot get the original version using the change manager');
        }

        return $change->getOld();
    }

    /**
     * @param string $field_id
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getPreviousValueForField($field_id)
    {
        $change = $this->getLastChangeForField($field_id);
        if (!$change) {
            throw new \InvalidArgumentException('Field was not modified, cannot get the original version using the change manager');
        }

        return $change->getOld();
    }

    /**
     * @param ChangeInterface $change
     */
    public function getMetaDataForChange(ChangeInterface $change)
    {
        $id = spl_object_hash($change);
        if (isset($this->change_metadata[$id])) {
            return $this->change_metadata[$id];
        }

        return;
    }

    /**
     * @param array $data An array of data. If metadata already exists it will be merged
     */
    public function setCurrentChangeMetadata(array $data)
    {
        if ($this->current_change_metadata) {
            $this->current_change_metadata = array_merge($this->current_change_metadata, $data);
        } else {
            $this->current_change_metadata = $data;
        }
    }

    public function clearCurrentChangeMetaData()
    {
        $this->current_change_metadata = null;
    }

    /**
     * A touched field is one that has been changed or tried to be changed.
     * Usually, if you set a value that is already set, we dont consider that
     * a change so there would be no record of that.
     *
     * Using touched fields, we store any assignment request so you can know if
     * a field set was ever attempted.
     *
     * The practical use for this is with triggers to determine if a field was "specified"
     *
     * @param string $field_id
     *
     * @return bool
     */
    public function hasTouchedField($field_id)
    {
        return isset($this->touched_fields[$field_id]);
    }

    /**
     * @return array
     */
    public function getTouchedFields()
    {
        return $this->touched_fields;
    }

    /**
     * Touch a field.
     *
     * @param string $field_id
     */
    public function touchField($field_id)
    {
        $this->touched_fields[$field_id] = true;
    }
}
