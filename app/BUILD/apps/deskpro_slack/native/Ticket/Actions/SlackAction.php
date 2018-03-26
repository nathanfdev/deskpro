<?php

/**
 * DeskPRO.
 *
 * @category Slack
 */

namespace deskpro_slack\Ticket\Actions;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractContainerAwareAction;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use Application\DeskPRO\Tickets\Actions\AppActionInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Class SlackAction.
 */
class SlackAction extends AbstractContainerAwareAction implements ActionInterface, AppActionInterface
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
            $context->getLogger()->debug(sprintf('[SlackAction] No app (app id: %d)', $this->getMetaData()->get('app_id')));

            return;
        }

        $message = $this->renderMessage($ticket, $context);
        $channel = $this->getActionOption('channel');

        $context->getLogger()->debug("[SlackAction] Sending message to channel: $channel");

        try {
            $client = new HttpClient();

            $client->request(
                'POST',
                $app->getSetting('webhook_url'),
                ['form_params' => ['payload' => $this->generatePayload($message)]]
            );
        } catch (\Exception $e) {
            $context->getLogger()->notice("[SlackAction] Error sending Slack message: {$e->getMessage()}");

            $ticket->getStateChangeRecorder()->recordData('app_message',
            ['app_id'              => $app->id, 'app_title' => $app->title, 'package_name' => $app->package->name,
                   'package_title' => $app->package->title, 'message' => "Failed sending message to channel \"$channel\"", ]);
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
        $stateChange = $ticket->getStateChangeRecorder();

        $fallback = '#'.$ticket->getId().' ';
        $fallback .= '<'.$this->getContainer()->getBrandSetting('core.deskpro_url').'agent/#app.tickets,t:'.$ticket->getId().'|'.htmlspecialchars($ticket->getSubject()).'> ';

        $messages = $stateChange->getNewUserReplies();
        $message  = array_pop($messages);
        if (!$message) {
            $message = $ticket->getLastReply();
        }

        if ($message) {
            $text = $message->getMessagePreviewText(160);
        } else {
            $text = $ticket->getSubject();
        }

        $attachment = [
            'color'      => '#1D7AB2',
            'text'       => htmlspecialchars($text),
            'title'      => '#'.$ticket->getId().' '.htmlspecialchars($ticket->getSubject()),
            'title_link' => $this->getContainer()->getBrandSetting('core.deskpro_url').'agent/#app.tickets,t:'.$ticket->getId(),
        ];

        if ($context->getEventType() == 'newticket') {
            $pretext = 'New ticket';
        } elseif ($context->getEventType() == 'newreply') {
            if ($stateChange->hasNewAgentNote()) {
                $pretext = 'New agent note';
            } elseif ($stateChange->hasNewAgentReply()) {
                $pretext = 'New agent reply';
            } else {
                $pretext = 'New user reply';
            }
        } else {
            $pretext = 'Ticket updated';
        }
        $attachment['pretext'] = $pretext;
        $fallback .= $pretext;
        if ($context->getPersonContext()) {
            $fallback .= ' by '.htmlspecialchars($context->getPersonContext()->getDisplayContact());
            $attachment['author_name'] = htmlspecialchars($context->getPersonContext()->getDisplayName());
            $attachment['author_icon'] = $context->getPersonContext()->getPictureUrl();
            $attachment['author_link'] = 'mailto:'.$context->getPersonContext()->getPrimaryEmailAddress();
        } else {
            $fallback .= ' by system';
        }

        // slack wants entities for unicode characters so convert them
        $fallback = Strings::htmlEntityEncodeUtf8($fallback);

        $attachment['fallback'] = $fallback;

        return $attachment;
    }

    /**
     * @param $message
     *
     * @return array
     */
    private function generatePayload($message)
    {
        $payload = [
            'attachments' => [$message],
            'username'    => 'DeskPro',
        ];
        if ($this->getActionOption('channel')) {
            $payload['channel'] = $this->getActionOption('channel');
        }

        return json_encode($payload);
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return Util::getBaseClassname($this).$this->getMetaData()->get('app_id', 0);
    }
}
