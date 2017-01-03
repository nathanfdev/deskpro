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

/**
 * Created by PhpStorm.
 * User: chroder
 * Date: 07/03/2014
 * Time: 12:30.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Person;
use Monolog\Logger;
use Orb\Util\OptionsArray;

interface ExecutorContextInterface
{
    /**
     * @return string
     */
    public function getEventMethod();

    /**
     * @return string
     */
    public function getEventPerformer();

    /**
     * @return Person
     */
    public function getPersonContext();

    /**
     * @return bool
     */
    public function hasEmailContext();

    /**
     * @param AbstractReader $reader
     */
    public function setEmailContext(AbstractReader $reader);

    /**
     * @return AbstractReader
     */
    public function getEmailContext();

    /**
     * @param string $event_performer
     */
    public function setEventPerformer($event_performer);

    /**
     * @return array
     */
    public function getEventMethodOptions();

    /**
     * @return string
     */
    public function getEventType();

    /**
     * @return Logger
     */
    public function getLogger();

    /**
     * @param Person $person
     * @param bool   $set_performer Automatically set the event performer based on this user
     */
    public function setPersonContext(Person $person, $set_performer = true);

    /**
     * @param $event_type
     */
    public function setEventType($event_type);

    /**
     * @return OptionsArray
     */
    public function getVars();

    /**
     * @return OptionsArray
     */
    public function getUserVars();

    /**
     * @param string $event_method         Event method (email, api, or web)
     * @param array  $event_method_options Event options (eg a URL etc)
     */
    public function setEventMethod($event_method, array $event_method_options = []);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getEventMethodOption($name);
}
