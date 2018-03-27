<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use JMS\Serializer\Annotation as Serializer;
use Orb\Util\Util;

/**
 * The basic entity class.
 *
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @method getId()
 *
 * @deprecated please see how DeskPRO\Bundle\AppBundle\Entity entities are declared using interfaces and traits for new entities
 */
abstract class DomainObject extends BasicDomainObject
{
    const API_MODE_OPT_OUT = 1;
    const API_MODE_OPT_IN  = 2;

    /** @var int */
    protected $_api_mode = self::API_MODE_OPT_OUT;

    /**
     * Causes error to be logged when the object is persisted.
     *
     * @var bool
     */
    private $_no_persist = false;

    /**
     * @var array
     */
    public $_presave_state = [];

    /**
     * @return \Doctrine\ORM\EntityRepository
     *
     * @deprecated
     */
    public static function getRepository()
    {
        $entity = get_called_class();
        $entity = explode('\\', $entity);
        $entity = array_pop($entity);

        $em = App::getOrm();

        return $em->getRepository("DeskPRO:$entity");
    }

    /**
     * Get the table name for this entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return App::getOrm()->getClassMetadata(get_called_class())->getTableName();
    }

    /**
     * @return string
     */
    public static function getEntityName()
    {
        $name = Util::getBaseClassname(get_called_class());
        if (preg_match('#^ApplicationDeskPROEntity(.*?)Proxy$#', $name, $m)) {
            $name = $m[1];
        }

        $name = 'DeskPRO:'.$name;

        return $name;
    }

    /**
     * Get an object ref for this entity. This is the table name and the entity ID.
     * For example, "tickets.1234".
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getObjectRef()
    {
        if (method_exists($this, 'getId')) {
            return $this->getTableName().'.'.$this->getId();
        } elseif (method_exists($this, 'getRef')) {
            return $this->getTableName().'.'.$this->getRef();
        } else {
            throw new \RuntimeException('Object does not implement getObjectRef');
        }
    }

    /**
     * Sets the value of a field, and calls the property changed tracker.
     *
     * @param $field
     * @param $value
     */
    protected function setModelField($field, $value)
    {
        $this->getStateChangeRecorder()->touchField($field);

        $old = null;
        if (property_exists($this, $field)) {
            $old = $this->$field;
        }

        // Detect fields that did not change
        if (is_null($value) && is_null($old)) {
            return;
        } elseif (is_scalar($value)) {
            if (is_numeric($value) && is_numeric($old)) {
                if ((string) $value === (string) $old) {
                    return;
                }
            } else {
                if ($value === $old) {
                    return;
                }
            }
        } elseif ($value instanceof \DateTime) {
            if ($old instanceof \DateTime && $value->getTimestamp() == $old->getTimestamp()) {
                return;
            }
        } elseif (is_object($value) && isset($value->id) && is_object($old) && isset($old->id)) {
            if ($value->id == $old->id) {
                return;
            }
        }

        $this->$field = $value;

        $this->_onPropertyChanged($field, $old, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $func = 'set'.str_replace('_', '', $offset);
        if (method_exists($this, $func) || $this->_isCustomCallable(strtolower($func))) {
            $this->$func($value);
        } else {
            $this->setModelField($offset, $value);
        }
    }

    /**
     * Sets a model field value but does not mark it as changed so it wont be persisted.
     *
     * @param string $field
     * @param mixed  $value
     */
    public function setUntrackedModelField($field, $value)
    {
        $this->$field = $value;
    }

    /**
     * @param bool  $primary
     * @param bool  $deep
     * @param array $visited
     *
     * @return array
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $repository = static::getRepository();
        if (!method_exists($repository, 'getFieldMappings')) {
            return [];
        }

        $values    = [];
        $visited[] = $this;

        foreach ($repository->getFieldMappings() as $name => $field) {
            if ($this->_api_mode == self::API_MODE_OPT_IN && empty($field['dpApi'])) {
                continue;
            } elseif ($this->_api_mode == self::API_MODE_OPT_OUT && isset($field['dpApi']) && !$field['dpApi']) {
                continue;
            }

            if (!empty($field['dpApiPrimary']) && !$primary) {
                continue;
            }

            if (method_exists($this, 'getreal'.$name)) {
                $val = $this->{'getreal'.$name}();
            } else {
                $val = $this[$name];
            }

            if ($val instanceof \DateTime || $field['type'] == 'datetime') {
                if ($val) {
                    $values[$name]           = $val->format('Y-m-d H:i:s');
                    $values["{$name}_ts"]    = $val->getTimestamp();
                    $values["{$name}_ts_ms"] = $val->getTimestamp() * 1000;
                } else {
                    $values[$name]           = null;
                    $values["{$name}_ts"]    = 0;
                    $values["{$name}_ts_ms"] = 0;
                }
            } else {
                $values[$name] = $val;

                if ($this instanceof HasPhraseName && ($name == 'title' || $name == 'name') && App::$container->getLanguageData()->isMultiLang()) {
                    $translated = [];
                    foreach (App::$container->getLanguageData()->getAll() as $l) {
                        $p = App::getTranslator()->getPhraseObject($this, $name, $l, false);
                        if ($p && $p != $val) {
                            $translated[] = ['language_id' => $l->id, 'language' => $l->sys_name, $name => $p];
                        }
                    }
                    if ($translated) {
                        $values[$name.'_translated'] = $translated;
                    }
                }
            }
        }

        if ($deep) {
            foreach ($repository->getAssociationMappings() as $name => $association) {
                if (empty($association['dpApi'])) {
                    continue;
                }

                if (!empty($association['dpApiPrimary']) && !$primary) {
                    continue;
                }

                $val = $this[$name];

                $subDeep = !empty($association['dpApiDeep']);
                if (in_array($val, $visited)) {
                    $subDeep = false;
                }

                if ($val instanceof self) {
                    $values[$name] = $val->toApiData(false, $subDeep, $visited);
                } elseif (is_array($val) || $val instanceof \Traversable) {
                    $output = [];

                    foreach ($val as $key => $sub) {
                        if ($sub instanceof \Application\DeskPRO\Domain\DomainObject) {
                            $output[$key] = $sub->toApiData(false, $subDeep, $visited);
                        }
                    }

                    $values[$name] = $output;
                } elseif ($val === null) {
                    $values[$name] = null;
                }
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    public function getScalarData()
    {
        $repository = static::getRepository();
        if (!method_exists($repository, 'getFieldMappings')) {
            return [];
        }

        $values = [];

        foreach ($repository->getFieldMappings() as $name => $field) {
            $val = $this[$name];

            if ($val instanceof \DateTime) {
                $values[$name] = $val->format('Y-m-d H:i:s');
            } elseif (is_array($val)) {
                $values[$name] = serialize($val);
            } else {
                $values[$name] = $val;
            }
        }

        return $values;
    }

    /**
     * Sets the special no persist flag that causes an error if this object is persisted.
     */
    public function _setNoPersist()
    {
        $this->_no_persist = true;
    }

    /**
     * Check the current status of the no persist flag.
     *
     * @return bool
     */
    public function _isNoPersist()
    {
        return $this->_no_persist;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $me = get_class($this);
        $me = explode('\\', $me);
        $me = array_pop($me);

        if (property_exists($this, 'id')) {
            if ($this->id) {
                return "<$me:#".$this->id.'>';
            } else {
                return "<$me:#0:".spl_object_hash($this).'>';
            }
        } else {
            return "<$me:".spl_object_hash($this).'>';
        }
    }

    public function persistTranslatable()
    {
        if (isset($this->_dp_object_translatable)) {
            $this->_dp_object_translatable->_dpTranslatePersistChanges();
        }
    }
}
