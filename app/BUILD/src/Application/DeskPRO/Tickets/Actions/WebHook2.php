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
class WebHook2 extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('url');
        $options->addValidNames('username', 'password', 'method', 'custom_data', 'headers', 'timeout', 'disable_cert');

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
        $custom_data = @json_decode($renderer->renderTicketTemplate($this->getActionOption('custom_data') ?: '', $ticket, $context), true);
        $username    = $renderer->renderTicketTemplate($this->getActionOption('username') ?: '', $ticket, $context);
        $password    = $renderer->renderTicketTemplate($this->getActionOption('password') ?: '', $ticket, $context);

        $http_client = new HttpClient(['timeout' => $timeout]);
        $method      = strtoupper($this->getActionOption('method')) ?: 'POST';
        $options     = [];

        if ($headers) {
            $options[RequestOptions::HEADERS] = Strings::parseEqualsLines($headers, Strings::EQUALSLINES_DUPE_ADD_ARRAY, ':');
        }
        if ($method == 'POST' || $method == 'PUT') {
            $options[RequestOptions::JSON] = $custom_data;
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
