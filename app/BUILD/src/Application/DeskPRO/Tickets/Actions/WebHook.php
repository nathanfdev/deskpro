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

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Twig\Extension\TemplatingExtension;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Execute a web hook.
 *
 * @option string url
 * @option string username
 * @option string password
 * @option string method
 * @option string custom_data
 * @option string headers
 * @option int    timeout
 */
class WebHook extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('url');
        $options->addValidNames('username', 'password', 'method', 'custom_data', 'headers', 'timeout', 'payload_type');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $timeout = intval($this->getActionOption('timeout')) ?: 20;

        /** @var TemplatingExtension $renderer */
        $renderer    = $this->getContainer()->getTwig()->getExtension('deskpro_templating');
        $url         = $renderer->renderTicketTemplate($this->getActionOption('url'), $ticket, $context);
        $headers     = $renderer->renderTicketTemplate($this->getActionOption('headers'), $ticket, $context);
        $custom_data = $renderer->renderTicketTemplate($this->getActionOption('custom_data') ?: '', $ticket, $context);
        $username    = $renderer->renderTicketTemplate($this->getActionOption('username') ?: '', $ticket, $context);
        $password    = $renderer->renderTicketTemplate($this->getActionOption('password') ?: '', $ticket, $context);

        $http_client = new Client(['timeout' => $timeout]);
        $method      = strtoupper($this->getActionOption('method')) ?: 'POST';
        $options     = [];

        if ($headers) {
            $headers                          = Strings::parseEqualsLines($headers, Strings::EQUALSLINES_DUPE_ADD_ARRAY, ':');
            $options[RequestOptions::HEADERS] = $headers;
        }

        if ($method == 'POST' || $method == 'PUT') {
            $data                    = [];
            $data['ticket']          = $ticket->toApiData();
            $data['person_context']  = $context->getPersonContext() ? $context->getPersonContext()->toApiData() : null;
            $data['event_performer'] = $context->getEventPerformer();
            $data['event_type']      = $context->getEventType();
            $data['event_method']    = $context->getEventMethod();
            $data['custom_data']     = $custom_data;

            $format = 'json' === $this->getActionOption('payload_type')
                ? RequestOptions::JSON
                : RequestOptions::FORM_PARAMS;
            $options[$format] = $data;
        }

        if ($username || $password) {
            $options[RequestOptions::AUTH] = [$username, $password];
        }

        try {
            $response = $http_client->request($method, $url, $options);
            $data     = [
                'url'     => $url,
                'reason'  => $response->getReasonPhrase(),
                'status'  => $response->getStatusCode(),
                'content' => (string) $response->getBody(),
            ];
            $ticket->getStateChangeRecorder()->recordData('webhook', $data);
        } catch (\Exception $e) {
            $exception = new \Exception('Trigger WebHook failed: '.$e->getMessage(), 0, $e);
            $this->getContainer()->get('dp_sys.alerts.event_logger')->log($exception);
            $data = [
                'url'     => $url,
                'reason'  => $e->getMessage(),
                'status'  => $e->getCode(),
                'content' => null,
            ];
            $ticket->getStateChangeRecorder()->recordData('webhook', $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
