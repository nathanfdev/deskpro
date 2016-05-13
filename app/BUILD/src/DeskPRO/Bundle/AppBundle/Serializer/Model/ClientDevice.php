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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\ClientDevice as ClientDeviceEntity;
use JMS\Serializer\Annotation as JMS;

class ClientDevice
{
    /**
     * @var int
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $person;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $device_id;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $device_type;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $device_agent;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $device_name;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $app_type;

    /**
     * @var bool
     * @JMS\Type("boolean")
     */
    protected $can_notify;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $notify_token;

    /**
     * @var \DateTime*
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * @param ClientDeviceEntity $clientDevice
     */
    public function __construct(ClientDeviceEntity $clientDevice)
    {
        $this->id           = $clientDevice->getId();
        $this->person       = $clientDevice->getPerson();
        $this->device_id    = $clientDevice->getDeviceId();
        $this->device_type  = $clientDevice->getDeviceType();
        $this->device_agent = $clientDevice->getDeviceAgent();
        $this->device_name  = $clientDevice->getDeviceName();
        $this->app_type     = $clientDevice->getAppType();
        $this->can_notify   = $clientDevice->canNotify();
        $this->notify_token = $clientDevice->canNotify() ? $clientDevice->getNotifyToken() : null;
        $this->date_created = $clientDevice->getDateCreated();
    }
}
