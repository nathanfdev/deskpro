<?php
/**
 * Created by PhpStorm.
 * User: chroder
 * Date: 07/03/2014
 * Time: 12:30
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
    public function setEventMethod($event_method, array $event_method_options = array());

    /**
     * @param  string $name
     * @return mixed
     */
    public function getEventMethodOption($name);
}
