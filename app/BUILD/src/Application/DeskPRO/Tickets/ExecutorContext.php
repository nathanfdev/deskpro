<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Person;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Orb\Util\OptionsArray;

class ExecutorContext implements ExecutorContextInterface
{
    const EVENT_NEW    = 'newticket';
    const EVENT_REPLY  = 'newreply';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';
    const EVENT_NOOP   = 'noop';

    const METHOD_API    = 'api';
    const METHOD_WEB    = 'web';
    const METHOD_EMAIL  = 'email';
    const METHOD_MOBILE = 'mobile';
    const METHOD_SMS    = 'sms';
    const METHOD_PHONE  = 'phone';

    /**
     * @var \Orb\Util\OptionsArray
     */
    private $vars;

    /**
     * @var \Orb\Util\OptionsArray
     */
    private $user_vars;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person_context;

    /**
     * @var string
     */
    private $event_type = 'update';

    /**
     * @var string
     */
    private $event_method = 'system';

    /**
     * @var array
     */
    private $event_method_options = [];

    /**
     * @var null
     */
    private $event_performer = 'system';

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct(Logger $logger = null)
    {
        if (!$logger) {
            // Null logger
            $logger = new Logger('ticket', [new NullHandler()]);
        }

        $this->vars      = new OptionsArray();
        $this->user_vars = new OptionsArray();
        $this->logger    = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Person $person
     * @param bool   $set_performer Automatically set the event performer based on this user
     */
    public function setPersonContext(Person $person, $set_performer = true)
    {
        $this->person_context = $person;

        if ($set_performer) {
            if ($this->person_context->is_agent) {
                $this->event_performer = 'agent';
            } else {
                $this->event_performer = 'user';
            }
        }
    }

    /**
     * @return Person
     */
    public function getPersonContext()
    {
        return $this->person_context;
    }

    /**
     * @return OptionsArray
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @return OptionsArray
     */
    public function getUserVars()
    {
        return $this->user_vars;
    }

    /**
     * @return bool
     */
    public function hasEmailContext()
    {
        return $this->vars->has('email_reader');
    }

    /**
     * @param AbstractReader $reader
     */
    public function setEmailContext(AbstractReader $reader)
    {
        $this->vars->set('email_reader', $reader);
    }

    /**
     * @throws \RuntimeException
     *
     * @return AbstractReader
     */
    public function getEmailContext()
    {
        if (!$this->vars->has('email_reader')) {
            throw new \RuntimeException('No email reader has been set');
        }

        return $this->vars->get('email_reader');
    }

    /**
     * The event type. This indicates what kind of triggers are fired as well.
     *
     * Possible values:
     * - newticket
     * - newreply
     * - update
     *
     * @param string $event_type
     *
     * @return $this
     */
    public function setEventType($event_type)
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * @return string newticket, newreply, update
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * The event method is how the event is being fired.
     *
     * @param string $event_method         Event method (email, api, or web)
     * @param array  $event_method_options Event options (eg a URL etc)
     */
    public function setEventMethod($event_method, array $event_method_options = [])
    {
        $this->event_method         = $event_method;
        $this->event_method_options = $event_method_options;
    }

    /**
     * @return string email, mobile, api or web
     */
    public function getEventMethod()
    {
        return $this->event_method;
    }

    /**
     * @return array
     */
    public function getEventMethodOptions()
    {
        return $this->event_method_options;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getEventMethodOption($name)
    {
        return isset($this->event_method_options[$name]) ? $this->event_method_options[$name] : null;
    }

    /**
     * @param string $event_performer
     */
    public function setEventPerformer($event_performer)
    {
        $this->event_performer = $event_performer;
    }

    /**
     * @return string system, user or agent
     */
    public function getEventPerformer()
    {
        return $this->event_performer;
    }
}
