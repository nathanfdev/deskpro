<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\EmailBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class SendmailSourceStatus implements NotifyPropertyChanged
{
    protected $id;

    protected $user_email;

    protected $event_type;

    protected $event_info;

    protected $details;

    protected $date_created;

    protected $source;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    //###########################################################################
    // Doctrine
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\EmailBundle\EntityRepository\SendmailSourceStatusRepository';
        $metadata->setPrimaryTable([
            'name'    => 'sendmail_source_statuses',
            'indexes' => [
            ],
            'uniqueConstraints' => [
            ],
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
        ]);
        $metadata->mapField([
            'columnName' => 'user_email',
            'fieldName'  => 'user_email',
        ]);
        $metadata->mapField([
            'columnName' => 'event_type',
            'fieldName'  => 'event_type',
        ]);
        $metadata->mapField([
            'columnName' => 'event_info',
            'fieldName'  => 'event_info',
        ]);
        $metadata->mapField([
            'columnName' => 'details',
            'fieldName'  => 'details',
            'type'       => 'text',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'source',
            'targetEntity' => 'Application\EmailBundle\Entity\SendmailSource',
            'dpApi'        => true,
            'joinColumns'  => [[
                'name'                 => 'sendmail_source_id',
                'referencedColumnName' => 'id',
                'onDelete'             => 'cascade',
            ]],
        ]);
    }

    public function toArray()
    {
        $data = [];

        $data['id']           = $this->id;
        $data['event']        = $this->event_type;
        $data['email']        = $this->user_email;
        $data['info']         = $this->event_info;
        $data['details']      = $this->details;
        $data['date_created'] = $this->date_created->format('Y-m-d H:i:s');

        return $data;
    }

    public function __getPropValue__($k)
    {
        return $this->$k;
    }
    public function __setPropValue__($k, $v)
    {
        $this->$k = $v;
    }
    public function __hasRunLoad__()
    {
        return true;
    }

    /** @var PropertyChangedListener[] */
    private $_listeners = [];
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->setModelField('_listeners[]', $listener);
    }

    private function setModelField($field, $value)
    {
        $old = null;
        if (property_exists($this, $field)) {
            $old = $this->$field;
        }

        // Detect fields that did not change
        if (is_null($value) && is_null($old)) {
            return;
        } elseif (is_scalar($value)) {
            if (is_numeric($value) && is_numeric($old)) {
                if ($value == $old) {
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

        foreach ($this->_listeners as $listener) {
            $listener->propertyChanged($this, $field, $old, $value);
        }
    }
}
