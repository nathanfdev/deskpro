<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting organization entity.
 *
 * Class Organization
 */
class Organization extends AbstractEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Blob
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
     * @var ContactData[]
     */
    private $contact_data;

    /**
     * @var Collection
     */
    private $custom_fields;

    /**
     * @var string[]
     */
    private $labels = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date_created  = new DateTime();
        $this->contact_data  = new Collection();
        $this->custom_fields = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ORGANIZATION;
    }

    /**
     * Set name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns picture.
     *
     * @return Blob|null
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set picture.
     *
     * @param Blob $picture
     *
     * @return $this
     */
    public function setPicture(Blob $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Set importance.
     *
     * @return int
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Returns importance.
     *
     * @param int $importance
     *
     * @return $this
     */
    public function setImportance($importance)
    {
        $this->importance = (int) $importance;

        return $this;
    }

    /**
     * Set date created.
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns date created.
     *
     * @param DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * Returns organization contact data.
     *
     * @return Collection|ContactData[]
     */
    public function getContactData()
    {
        return $this->contact_data;
    }

    /**
     * Add an organization contact data.
     *
     * @param ContactData $contact
     *
     * @return $this
     */
    public function addContact(ContactData $contact)
    {
        $this->contact_data->attach($contact);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * @param CustomField $custom_field
     *
     * @return $this
     */
    public function addCustomField(CustomField $custom_field)
    {
        $this->custom_fields->attach($custom_field);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;

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
            'oid'            => $this->oid,
            'import_map_key' => $this->import_map_key,
            'name'           => $this->name,
            'picture'        => $this->picture ? $this->picture->toArray() : null,
            'importance'     => $this->importance,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'contact_data'   => $this->contact_data->entitiesToArray(),
            'custom_fields'  => $this->custom_fields->entitiesToArray(),
            'labels'         => $this->labels,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('name', new Constraints\NotBlank())
            ->addPropertyConstraint('contact_data', new Constraints\Valid())
            ->addPropertyConstraint('custom_fields', new Constraints\Valid())
        ;
    }
}
