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

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ContactData.
 */
class ContactData
{
    /**
     * @var Address[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\Address>")
     *
     * @Assert\Valid()
     */
    private $address = [];

    /**
     * @var Facebook[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\Facebook>")
     *
     * @Assert\Valid()
     */
    private $facebook = [];

    /**
     * @var InstantMessage[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\InstantMessage>")
     *
     * @Assert\Valid()
     */
    private $instantMessage = [];

    /**
     * @var LinkedIn[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\LinkedIn>")
     *
     * @Assert\Valid()
     */
    private $linkedIn = [];

    /**
     * @var Phone[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone>")
     *
     * @Assert\Valid()
     */
    private $phone = [];

    /**
     * @var Twitter[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\Twitter>")
     *
     * @Assert\Valid()
     */
    private $twitter = [];

    /**
     * @var Website[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ContactData\Website>")
     *
     * @Assert\Valid()
     */
    private $website = [];

    /**
     * @return Address[]
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address[] $address
     */
    public function setAddress(array $address)
    {
        $this->address = $address;
    }

    /**
     * @param Address $address
     *
     * @return $this
     */
    public function addAddress(Address $address)
    {
        $this->address[] = $address;

        return $this;
    }

    /**
     * @return Facebook[]
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param Facebook[] $facebook
     */
    public function setFacebook(array $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @param Facebook $facebook
     *
     * @return $this
     */
    public function addFacebook(Facebook $facebook)
    {
        $this->facebook[] = $facebook;

        return $this;
    }

    /**
     * @return InstantMessage[]
     */
    public function getInstantMessage()
    {
        return $this->instantMessage;
    }

    /**
     * @param InstantMessage[] $instantMessage
     */
    public function setInstantMessage(array $instantMessage)
    {
        $this->instantMessage = $instantMessage;
    }

    /**
     * @param InstantMessage $instantMessage
     *
     * @return $this
     */
    public function addInstantMessage(InstantMessage $instantMessage)
    {
        $this->instantMessage[] = $instantMessage;

        return $this;
    }

    /**
     * @return LinkedIn[]
     */
    public function getLinkedIn()
    {
        return $this->linkedIn;
    }

    /**
     * @param LinkedIn[] $linkedIn
     */
    public function setLinkedIn(array $linkedIn)
    {
        $this->linkedIn = $linkedIn;
    }

    /**
     * @param LinkedIn $linkedIn
     *
     * @return $this
     */
    public function addLinkedIn(LinkedIn $linkedIn)
    {
        $this->linkedIn[] = $linkedIn;

        return $this;
    }

    /**
     * @return Phone[]
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param Phone[] $phone
     */
    public function setPhone(array $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param Phone $phone
     *
     * @return $this
     */
    public function addPhone(Phone $phone)
    {
        $this->phone[] = $phone;

        return $this;
    }

    /**
     * @return Twitter[]
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param Twitter[] $twitter
     */
    public function setTwitter(array $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @param Twitter $twitter
     *
     * @return $this
     */
    public function addTwitter(Twitter $twitter)
    {
        $this->twitter[] = $twitter;

        return $this;
    }

    /**
     * @return Website[]
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param Website[] $website
     */
    public function setWebsite(array $website)
    {
        $this->website = $website;
    }

    /**
     * @param Website $website
     *
     * @return Website
     */
    public function addWebsite(Website $website)
    {
        $this->website[] = $website;

        return $website;
    }
}
