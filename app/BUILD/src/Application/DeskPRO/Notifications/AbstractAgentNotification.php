<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAgentNotification
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $notifyList;

    /**
     * @var string
     */
    protected $client = 'sys';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    abstract public function shouldSendBrowserNotification(Person $person);
    abstract public function shouldSendEmailNotification(Person $person);
    abstract public function send();

    public function __construct()
    {
        $this->em              = App::getOrm();
        $this->eventDispatcher = App::get('event_dispatcher');
    }

    public function setCreatedClient($client)
    {
        $this->client = $client;
    }

    /**
     * Build a list of agents to email and browser notify.
     *
     * Returns an array with two sub-arrays of agent_id=>agent:
     * - (array) 'email': agent_id=>Person
     * - (array) 'browser': agent_id=>Person
     *
     * @return array
     */
    public function getNotifyList()
    {
        if ($this->notifyList) {
            return $this->notifyList;
        }
        $agents = $this->em->getRepository(Person::class)->getAgents();

        $onlineIds = $this->em->getRepository(Session::class)->getAvailableAgentIds();

        $sendBrowser = [];
        $sendEmail   = [];

        foreach ($onlineIds as $aid) {
            if (!isset($agents[$aid])) {
                continue;
            }
            $agent = $agents[$aid];
            if ($this->shouldSendBrowserNotification($agent)) {
                $sendBrowser[$aid] = $agent;
            }
        }

        foreach ($agents as $agent) {
            if ($this->shouldSendEmailNotification($agent)) {
                $sendEmail[$agent->getId()] = $agent;
            }
        }

        $this->notifyList = [
            'email'   => $sendEmail,
            'browser' => $sendBrowser,
        ];

        return $this->notifyList;
    }

    /**
     * Send an email notification to agents from the build list.
     *
     * @param string $tpl
     * @param array  $vars
     */
    public function sendEmailNotifications($tpl, array $vars)
    {
        $notifyList = $this->getNotifyList();

        if (!$notifyList['email']) {
            return;
        }

        foreach ($notifyList['email'] as $agent) {
            $message = App::getMailer()->createMessage();
            $message->setTemplate($tpl, $vars);
            $message->setToPerson($agent);
            App::getMailer()->send($message);
        }
    }

    /**
     * Send an email notification to agents from the build list using SendmailBundle.
     *
     * @param EmailBaseType $viewModel
     */
    public function sendNewEmailNotifications($viewModel)
    {
        $notifyList = $this->getNotifyList();

        if (!$notifyList['email']) {
            return;
        }

        foreach ($notifyList['email'] as $agent) {
            App::$container->get('email.email_sender')
                ->send($viewModel, ['to' => $agent]);
        }
    }

    /**
     * Send a browser notification to agents from the built list.
     *
     * $vars should include a 'notify_data' array that'll be data in the client message. This should at least
     * include a notify_type.
     *
     * @param string $tpl  The template to display in the browser for the notification
     * @param array  $vars Vars used in the template, and a notify_data to be inserted into the client message data
     */
    public function sendBrowserNotifications($tpl, array $vars)
    {
        $notifyList = $this->getNotifyList();

        if (!$notifyList['browser']) {
            return;
        }

        foreach ($notifyList['browser'] as $agent) {
            $tplLine = App::getTemplating()->render($tpl, $vars);

            $data           = $vars['notify_data'];
            $data['row']    = $tplLine;
            $data['target'] = $agent->getId();

            $this->eventDispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent-notify.'.$data['notify_type'], $data)
            );
        }
    }
}
