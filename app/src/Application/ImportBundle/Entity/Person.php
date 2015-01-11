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

namespace Application\ImportBundle\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting person entity
 *
 * Class Person
 * @package Application\ImportBundle\Entity
 */
final class Person extends AbstractEntity
{
    /**
     * @var int
     */
    private $oid;

    /**
     * @var bool
     */
    private $is_agent = false;

    /**
     * Can we merge isAgent and isUser?
     *
     * @var bool
     */
    private $is_user = false;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * Should we use name or just first name?
     *
     * @var string
     */
    private $name;

    /**
     * @var string or int? Can we merge it to date created?
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var array
     */
    private $emails = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PERSON;
    }

    /**
     * @return int
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param int $oid
     * @return $this
     */
    public function setOid($oid)
    {
        $this->oid = (int)$oid;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * @param boolean $is_agent
     * @return $this
     */
    public function setAsAgent($is_agent)
    {
        $this->is_agent = (bool)$is_agent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUser()
    {
        return $this->is_user;
    }

    /**
     * @param boolean $is_user
     * @return $this
     */
    public function setAsUser($is_user)
    {
        $this->is_user = (bool)$is_user;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return $this
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function addEmail($email)
    {
        $this->emails[] = $email;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'          => $this->oid,
            'is_agent'     => $this->is_agent,
            'is_user'      => $this->is_user,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'name'         => $this->name,
            'timezone'     => $this->timezone,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
            'emails'       => $this->emails,
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
    }
}
