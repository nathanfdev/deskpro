<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Articles that need to be created.
 *
 * @JMS\ExclusionPolicy("none")
 */
class ArticlePendingCreate extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $assigned_person = null;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket = null;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketMessage>")
     *
     * @var \Application\DeskPRO\Entity\TicketMessage
     */
    protected $message = null;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $comment = '';

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticket ? $this->ticket->getId() : 0;
    }

    public function _sendUpdates()
    {
        App::getContainer()->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.ui.new-pending',
            [
                'id'        => $this->id,
                'ticket_id' => $this->ticket ? $this->ticket->getId() : 0,
            ]
        ));
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticlePendingCreate';
        $metadata->setPrimaryTable(['name' => 'article_pending_create']);
        $metadata->addLifecycleCallback('_sendUpdates', 'postPersist');
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'comment',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'comment',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'assigned_person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'assigned_person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'message',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_message_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
