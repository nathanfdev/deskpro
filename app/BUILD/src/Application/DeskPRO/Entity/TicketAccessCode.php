<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * For each participant on a ticket, they get an access code. Normally user
 * participants dont use the TAC because they all share the public TAC that is set
 * on the ticket itself via CC'ing. But agents always use a TAC.
 *
 * So there's TAC's (this) and PTAC's (public ticket access code) that is attached to the ticket.
 */
class TicketAccessCode extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var int
     */
    protected $auth;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $len        = Ticket::TAC_AUTHCODE_LEN;
        $this->auth = DpStrings::random($len, Strings::CHARS_KEY);
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Encodes the ticket ID and the auth into a single string.
     *
     * @return string
     */
    public function getAccessCode()
    {
        $str = Util::baseEncode($this->id, 'letters');
        $str .= $this->auth;

        return $str;
    }

    /**
     * Get the Message-ID field for an email regarding this ticket, witht he
     * embedded TAC code.
     *
     * @return string
     */
    public function getUniqueEmailMessageId()
    {
        $uid = 'TAC-'.$this->getAccessCode().'.';
        $uid .= uniqid('', true).'-'.App::getSetting('core.site_id');
        $uid .= '@'.md5(App::getSetting('core.site_url', 'deskpro'));

        return $uid;
    }

    /**
     * Decoes an access code into a ticket id and the standalone code. You can look
     * up the record later to verify, get the user etc.
     *
     * @param  $access_code
     *
     * @return array
     */
    public static function decodeAccessCode($access_code)
    {
        $len = Ticket::TAC_AUTHCODE_LEN;

        if (strlen($access_code) < ($len + 1)) {
            return false;
        }

        $matches = Strings::extractRegexMatch('#^(.+)(.{'.$len.'})$#', $access_code, -1);
        if (!$matches) {
            return false;
        }

        list(, $access_code_id, $auth) = $matches;

        $access_code_id = Util::baseDecode($access_code_id, 'letters');

        return [
            'access_code_id' => $access_code_id,
            'auth'           => $auth,
        ];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketAccessCode';
        $metadata->setPrimaryTable(['name' => 'ticket_access_codes']);
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
            'fieldName'  => 'auth',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'auth',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'mappedBy'     => null,
            'inversedBy'   => 'access_codes',
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
    }
}
