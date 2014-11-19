<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Log\Loggable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Application\DeskPRO\Log\Event\Base as BaseLogEvent;

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
        $this['api_key'] = $apiKey ? $apiKey->code : null;
        $this->person = $person;
        $this->children = new ArrayCollection();

        $this->_event = $event;
    }

    public function getEventObject()
    {
        return $this->_event;
    }

    public function prepare()
    {
        $this['event'] = $this->_event->getName();
        $this['details'] = $this->_event->getDetails();

        if ($subject = $this->_event->getSubject()) {
            $class = explode('\\', get_class($subject));
            $this['subject'] = end($class);
            $this['subject_id'] = $subject['id'];
        }
    }

    /**
     * todo
     * @return string
     */
    public function __toString()
    {
        return sprintf('Changelog event: ', $this['event']);
    }

    public function context()
    {
        return array();
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setPrimaryTable(array(
            'name' => 'log_event',
            'indexes' => array(
                'subject' => array('columns' => array('subject', 'subject_id')),
            )
        ));
        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'id' => true, 'options' => array('unsigned' => true)));
        $metadata->mapField(array( 'fieldName' => 'timestamp', 'type' => 'integer', 'nullable' => false, 'options' => array('unsigned' => true)));
        $metadata->mapField(array( 'fieldName' => 'event', 'type' => 'string', 'nullable' => false));
        $metadata->mapField(array( 'fieldName' => 'subject', 'type' => 'string', 'nullable' => true));
        $metadata->mapField(array( 'fieldName' => 'api_key', 'type' => 'string', 'nullable' => true));
        $metadata->mapField(array( 'fieldName' => 'subject_id', 'type' => 'integer', 'nullable' => true, 'options' => array('unsigned' => true)));
        $metadata->mapField(array( 'fieldName' => 'details', 'type' => 'array', 'nullable' => false));

        $metadata->mapManyToOne(array(
            'fieldName' => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\LogEvent',
            'joinColumns' => array(0 => array(
                'nullable' => true,
                'onDelete' => 'cascade',
            ),),
        ));

        $metadata->mapOneToMany(array(
            'fieldName' => 'children',
            'mappedBy'  => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\LogEvent',
        ));

        $metadata->mapManyToOne(array(
            'fieldName' => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns' => array(0 => array(
                'nullable' => true,
                'onDelete' => 'cascade',
            ),),
        ));

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
