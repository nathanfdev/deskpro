<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_highrise;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;
use Orb\Service\Highrise\Highrise;
use Orb\Service\Highrise\Resource\Person as HighrisePerson;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleAgentRequest(AgentRequestContext $context)
    {
        if ($context->getAction() == 'find-email') {
            return $this->callApiAction($context);
        } else {
            throw $context->createNotFoundException();
        }
    }

    /**
     * @param AgentRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function callApiAction(AgentRequestContext $context)
    {
        $url   = $context->getAppSetting('api_url');
        $token = $context->getAppSetting('api_token');

        if (!$url || !$token) {
            return $context->createJsonResponse(['error' => 'API token or URL missing. Please configure the plugin.']);
        }

        $parts = parse_url($url);
        $url   = $parts['scheme'].'://'.$parts['host'];

        $matches = [];

        $email = $context->getIn()->getString('email');
        if ($email) {
            $highrise  = new Highrise($url, $token);
            $personApi = new HighrisePerson($highrise);
            try {
                $error = error_reporting();
                error_reporting($error & ~E_WARNING);

                $output = $personApi->findPeopleWithCriteria(['email' => $email]);

                error_reporting($error);
            } catch (\Exception $e) {
                return $context->createJsonResponse(['error' => 'Invalid Highrise API URL or token.', 'error_type' => get_class($e), 'error_code' => $e->getCode(), 'error_message' => $e->getMessage()]);
            }

            foreach ($output as $person) {
                if (isset($person['first-name'], $person['last-name'])) {
                    $name = $person['first-name'].' '.$person['last-name'];
                } elseif (isset($person['first-name'])) {
                    $name = $person['first-name'];
                } elseif (isset($person['last-name'])) {
                    $name = $person['last-name'];
                } else {
                    $name = 'Unknown';
                }

                if (isset($person['contact-data']['email-addresses'][0]['address'])) {
                    $email = $person['contact-data']['email-addresses'][0]['address'];
                } else {
                    $email = 'Unknown';
                }

                if (isset($person['title'], $person['company-name'])) {
                    $companyTitle = $person['title'].' @ '.$person['company-name'];
                } elseif (isset($person['title'])) {
                    $companyTitle = $person['title'];
                } elseif (isset($person['company-name'])) {
                    $companyTitle = $person['company-name'];
                } else {
                    $companyTitle = false;
                }

                $matches[] = [
                    'id'      => $person['id'],
                    'name'    => $name,
                    'email'   => $email,
                    'title'   => isset($person['title']) ? $person['title'] : '',
                    'company' => isset($person['company-name']) ? $person['company-name'] : '',
                    'profile' => $url.'/people/'.$person['id'],
                ];
            }
        }

        return $context->createJsonResponse(['matched' => count($matches), 'matches' => $matches]);
    }
}
