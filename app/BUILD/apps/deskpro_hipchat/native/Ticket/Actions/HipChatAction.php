<?php

/**
 * DeskPRO.
 *
 * @category HipChat
 */

namespace deskpro_hipchat\Ticket\Actions;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractContainerAwareAction;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use Application\DeskPRO\Tickets\Actions\AppActionInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Strings;
use Orb\Util\Util;

class HipChatAction extends AbstractContainerAwareAction implements ActionInterface, AppActionInterface
{
    /**
     * @var
     */
    private $app;

    /**
     * @return AppInstance
     */
    private function getApp()
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app   = false;
        $app_manager = $this->getContainer()->getAppManager();
        $app_id      = $this->getMetaData()->get('app_id', 0);

        if ($app_manager->hasApp($app_id)) {
            $this->app = $app_manager->getApp($app_id);
        }

        return $this->app === false ? null : $this->app;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $app = $this->getApp();
        if (!$app) {
            $context->getLogger()->debug(sprintf('[HipChatAction] No app (app id: %d)', $this->getMetaData()->get('app_id')));

            return;
        }

        $message = $this->renderMessage($ticket, $context);
        $room_id = $this->getActionOption('room');

        $context->getLogger()->debug("[HipChatAction] Sending message to room: $room_id");

        try {
            $api = new \HipChatApi(
                $app->getSetting('api_token'),
                $app->getSetting('api_target', null) ?: \HipChatApi::DEFAULT_TARGET
            );
            $api->message_room(
                $room_id,
                'Deskpro',
                $message,
                $app->getSetting('notify')
            );

            $ticket->getStateChangeRecorder()->recordData('app_message', [
                'app_id'        => $app->id,
                'app_title'     => $app->title,
                'package_name'  => $app->package->name,
                'package_title' => $app->package->title,
                'message'       => "Send message to room \"$room_id\"",
            ]);
        } catch (\Exception $e) {
            $context->getLogger()->notice("[HipChatAction] Error sending HipChat message: {$e->getMessage()}");

            $ticket->getStateChangeRecorder()->recordData('app_message',
            ['app_id'              => $app->id, 'app_title' => $app->title, 'package_name' => $app->package->name,
                   'package_title' => $app->package->title, 'message' => "Failed sending message to room \"$room_id\"", ]);
        }
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return string
     */
    public function renderMessage(Ticket $ticket, ExecutorContextInterface $context)
    {
        $statechange = $ticket->getStateChangeRecorder();

        $message = '#'.$ticket->id.' <a href="'.$this->getContainer()->getBrandSetting('core.deskpro_url').'agent/#app.tickets,t:'.$ticket->id.'">';
        $message .= htmlspecialchars($ticket->subject);
        $message .= '</a><br/>';

        if ($context->getEventType() == 'newticket') {
            $message .= 'New ticket';
        } elseif ($context->getEventType() == 'newreply') {
            if ($statechange->hasNewAgentNote()) {
                $message .= 'New agent note';
            } elseif ($statechange->hasNewAgentReply()) {
                $message .= 'New agent reply';
            } else {
                $message .= 'New user reply';
            }
        } else {
            $message .= 'Ticket updated';
        }
        if ($context->getPersonContext()) {
            $message .= ' by '.htmlspecialchars($context->getPersonContext()->getDisplayContact());
        } else {
            $message .= ' by system';
        }

        // hipchat wants entities for unicode characters so convert them
        $message = Strings::htmlEntityEncodeUtf8($message);

        return $message;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return Util::getBaseClassname($this).$this->getMetaData()->get('app_id', 0);
    }
}
