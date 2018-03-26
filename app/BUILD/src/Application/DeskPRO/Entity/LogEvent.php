<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Log\Event\Base as BaseLogEvent;
use Application\DeskPRO\Log\Loggable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class LogEvent extends DomainObject implements Loggable
{
    protected $id;

    protected $timestamp;

    /** @var Person context person */
    protected $person;

    protected $parent;

    protected $children;

    protected $event;

    protected $subject;

    protected $subject_id;

    protected $details;

    protected $api_key;

    /** @var BaseLogEvent */
    protected $_event;

    public function __construct(BaseLogEvent $event, Person $person = null, ApiKey $apiKey = null)
    {
        $this['timestamp'] = time();
        $this['api_key']   = $apiKey ? $apiKey->code : null;
        $this->person      = $person;
        $this->children    = new ArrayCollection();

        $this->setModelField('_event', $event);
    }

    public function getEventObject()
    {
        return $this->_event;
    }

    public function prepare()
    {
        $this['event']   = $this->_event->getName();
        $this['details'] = $this->_event->getDetails();

        if ($subject = $this->_event->getSubject()) {
            $class              = explode('\\', get_class($subject));
            $this['subject']    = end($class);
            $this['subject_id'] = $subject['id'];
        }
    }

    /**
     * todo.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('Changelog event: ', $this['event']);
    }

    public function context()
    {
        return [];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setPrimaryTable([
            'name'    => 'log_event',
            'indexes' => [
                'subject' => ['columns' => ['subject', 'subject_id']],
            ],
        ]);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'id' => true, 'options' => ['unsigned' => true]]);
        $metadata->mapField(['fieldName' => 'timestamp', 'type' => 'integer', 'nullable' => false, 'options' => ['unsigned' => true]]);
        $metadata->mapField(['fieldName' => 'event', 'type' => 'string', 'nullable' => false]);
        $metadata->mapField(['fieldName' => 'subject', 'type' => 'string', 'nullable' => true]);
        $metadata->mapField(['fieldName' => 'api_key', 'type' => 'string', 'nullable' => true]);
        $metadata->mapField(['fieldName' => 'subject_id', 'type' => 'integer', 'nullable' => true, 'options' => ['unsigned' => true]]);
        $metadata->mapField(['fieldName' => 'details', 'type' => 'array', 'nullable' => false]);

        $metadata->mapManyToOne([
            'fieldName'    => 'parent',
            'targetEntity' => self::class,
            'joinColumns'  => [0 => [
                'nullable' => true,
                'onDelete' => 'cascade',
            ]],
        ]);

        $metadata->mapOneToMany([
            'fieldName'    => 'children',
            'mappedBy'     => 'parent',
            'targetEntity' => self::class,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'joinColumns'  => [0 => [
                'nullable' => true,
                'onDelete' => 'cascade',
            ]],
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
