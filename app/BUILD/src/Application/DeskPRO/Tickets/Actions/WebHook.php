<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
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
        $options->addValidNames('username', 'password', 'method', 'custom_data', 'headers', 'timeout', 'payload_type', 'disable_cert');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $timeout     = intval($this->getActionOption('timeout')) ?: 20;
        $disableCert = (bool) $this->getActionOption('disable_cert');

        $renderer    = $this->getContainer()->get('twig_template_renderer');
        $url         = $renderer->renderTicketTemplate($this->getActionOption('url'), $ticket, $context);
        $headers     = $renderer->renderTicketTemplate($this->getActionOption('headers'), $ticket, $context);
        $custom_data = $renderer->renderTicketTemplate($this->getActionOption('custom_data') ?: '', $ticket, $context);
        $username    = $renderer->renderTicketTemplate($this->getActionOption('username') ?: '', $ticket, $context);
        $password    = $renderer->renderTicketTemplate($this->getActionOption('password') ?: '', $ticket, $context);

        $http_client = new HttpClient(['timeout' => $timeout]);
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
            $data['ticket_logs']     = [];

            $state   = $ticket->getStateChangeRecorder();
            $changes = $state->getChanges();

            $logGenerator = new TicketLogGenerator($ticket, $context);
            foreach ($changes as $change) {
                $logData = $logGenerator->getLogDataForChange($change);
                if ($logData) {
                    $data['ticket_logs'][] = $logData;
                }
            }

            $format = 'json' === $this->getActionOption('payload_type')
                ? RequestOptions::JSON
                : RequestOptions::FORM_PARAMS;
            $options[$format] = $data;
        }

        if ($username || $password) {
            $options[RequestOptions::AUTH] = [$username, $password];
        }
        if ($disableCert) {
            $options[RequestOptions::VERIFY] = false;
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
