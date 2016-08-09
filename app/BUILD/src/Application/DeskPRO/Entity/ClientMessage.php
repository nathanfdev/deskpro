<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\ClientMessage\MessageHandler\BasicArray;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * A client message is somethign we send to the browser.
 *
 * All messages are stored here, even if a user has a socket connection.
 * Message contents are rendered by the handlers, and the content differs
 * depending on the context (socket, ajax poll, mobile push etc). See the Handlers
 * for information about that.
 *
 * The point for this is that in some cases, the event is important, but the data the event
 * might represent may not be important. Or in most cases, the client may want to fetch the data
 * for the event later.
 *
 * For example, if a given element is not currently loaded or in view,
 * then we may want to defer fetching information until later when the view is activated.
 * So in that case, the original context would just push an ID of this client message,
 * and the client would later request the full information as an HTTP request or by pushing
 * the ID through the socket.
 */
class ClientMessage extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * The channel the message is placed in.
     *
     * @var string
     */
    protected $channel;

    /**
     * The auth is used when a client wants to fetch a "full" answer, if the
     * original push sent only a short.
     *
     * @var string
     */
    protected $auth;

    /**
     * Data to give the handler.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The client ID (usully sessionid) that created this message.
     * This is so when we fetch messages, we don't get our own messages back.
     */
    protected $created_by_client = '';

    /**
     * The client ID (usully sessionid) that this message is for
     * specifically.
     */
    protected $for_client;

    /**
     * Who this message is for specifically.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $for_person;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->auth         = DpStrings::random(15, Strings::CHARS_KEY);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->setModelField('channel', $channel);

        return $this;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->setModelField('data', $data);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $client
     *
     * @return $this
     */
    public function setCreatedByClient($client)
    {
        $this->setModelField('created_by_client', $client);

        return $this;
    }

    /**
     * @param Person $for_person
     *
     * @return $this
     */
    public function setForPerson(Person $for_person = null)
    {
        $this->setModelField('for_person', $for_person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getForPerson()
    {
        return $this->for_person;
    }

    /**
     * @return mixed
     */
    public function getCreatedByClient()
    {
        return $this->created_by_client;
    }

    /**
     * @return mixed
     */
    public function getForClient()
    {
        return $this->for_client;
    }

    public function getHandler()
    {
        $handler = new BasicArray($this);

        return $handler;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Should be done in a separate listener.
     * Used deprecated App::get('event_dispatcher') for now.
     *
     * todo refactor
     */
    public function notifyMessageServers()
    {
        static $dispatcher;
        if (null === $dispatcher) {
            if (App::has('event_dispatcher')) {
                $dispatcher = App::get('event_dispatcher');
            } else {
                $dispatcher = false;
            }
        }

        if ($dispatcher) {
            $event = new \Application\DeskPRO\ClientMessage\Event($this);
            $dispatcher->dispatch('DeskPRO_onNewClientMessage', $event);
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ClientMessage';
        $metadata->setPrimaryTable(['name' => 'client_messages']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('notifyMessageServers', 'postPersist');
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
            'fieldName'  => 'channel',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'channel',
        ]);
        $metadata->mapField([
            'fieldName'  => 'auth',
            'type'       => 'string',
            'length'     => 15,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'auth',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'created_by_client',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'created_by_client',
        ]);
        $metadata->mapField([
            'fieldName'  => 'for_client',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'for_client',
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
            'fieldName'    => 'for_person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'for_person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
