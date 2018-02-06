<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting organization entity.
 *
 * Class Organization
 */
class Organization implements LabelAwareModelInterface, CustomDataAwareModelInterface, PrimaryImportModelInterface, ContactDataAwareModelInterface
{
    use PrimaryImportModelTrait, LabelAwareTrait, CustomDataAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var Blob
     *
     * @JMS\Type("DeskPRO\Bundle\ImportBundle\Model\Blob")
     *
     * @Assert\Valid()
     */
    private $picture;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $importance;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $emailDomains = [];

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_created;

    /**
     * @var ContactData
     *
     * @JMS\Type("DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData")
     *
     * @Assert\Valid()
     */
    private $contact_data;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contact_data = new ContactData();
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
        return $this->importance ?: 1;
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
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns date created.
     *
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContactData()
    {
        return $this->contact_data;
    }

    /**
     * @return array
     */
    public function getEmailDomains()
    {
        return $this->emailDomains;
    }

    /**
     * @param array $emailDomains
     *
     * @return $this
     */
    public function setEmailDomains($emailDomains)
    {
        $this->emailDomains = $emailDomains;

        return $this;
    }
}
