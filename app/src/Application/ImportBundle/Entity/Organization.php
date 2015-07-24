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
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting organization entity
 *
 * Class Organization
 * @package Application\ImportBundle\Entity
 */
class Organization extends AbstractEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Attachment
     */
    private $picture;

    /**
     * @var int
     */
    private $importance;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var OrganizationContactData[]
     */
    private $contact_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date_created = new DateTime();
        $this->contact_data = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ORGANIZATION;
    }

    /**
     * Set name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns picture
     *
     * @return Attachment
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set picture
     *
     * @param Attachment $picture
     * @return $this
     */
    public function setPicture(Attachment $picture = null)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * Set importance
     *
     * @return int
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Returns importance
     *
     * @param int $importance
     * @return $this
     */
    public function setImportance($importance)
    {
        $this->importance = (int)$importance;
        return $this;
    }

    /**
     * Set date created
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns date created
     *
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * Returns organization contact data
     *
     * @return Collection|OrganizationContactData[]
     */
    public function getContactData()
    {
        return $this->contact_data;
    }

    /**
     * Add an organization contact data
     *
     * @param OrganizationContactData $contact
     * @return $this
     */
    public function addContact(OrganizationContactData $contact)
    {
        $this->contact_data->attach($contact);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if ( ! $this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        $contact_data = array();
        foreach ($this->contact_data as $contact) {
            /** @var OrganizationContactData $contact */
            $contact_data[] = $contact->toArray();
        }

        return array(
            'oid'          => $this->oid,
            'name'         => $this->name,
            'picture'      => $this->picture ? $this->picture->toArray() : null,
            'importance'   => $this->importance,
            'contact_data' => $contact_data,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);
    }
}
