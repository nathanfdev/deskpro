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

namespace Application\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ContactData.
 */
class ContactData
{
    /**
     * @var Address[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\Address>")
     */
    private $address = [];

    /**
     * @var Facebook[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\Facebook>")
     */
    private $facebook = [];

    /**
     * @var InstantMessage[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\InstantMessage>")
     */
    private $instantMessage = [];

    /**
     * @var LinkedIn[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\LinkedIn>")
     */
    private $linkedIn = [];

    /**
     * @var Phone[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\Phone>")
     */
    private $phone = [];

    /**
     * @var Twitter[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\Twitter>")
     */
    private $twitter = [];

    /**
     * @var Website[]
     *
     * @JMS\Type("array<Application\ImportBundle\Model\ContactData\Website>")
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
    public function setAddress($address)
    {
        $this->address = $address;
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
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
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
    public function setInstantMessage($instantMessage)
    {
        $this->instantMessage = $instantMessage;
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
    public function setLinkedIn($linkedIn)
    {
        $this->linkedIn = $linkedIn;
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
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
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
    public function setWebsite($website)
    {
        $this->website = $website;
    }
}
