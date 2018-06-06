<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_zapier\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleAgentRequest(AgentRequestContext $context)
    {
        if ($context->getAction() == 'call-api') {
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
        $url  = $context->getAppSetting('url');
        $user = $context->getAppSetting('api_user');
        $key  = $context->getAppSetting('api_key');

        if (!$url || !$user || !$key) {
            return $context->createJsonResponse(['error' => 'API URL, user or key missing. Please configure the plugin.']);
        }

        if (!class_exists('\SoapClient')) {
            return $context->createJsonResponse(['error' => 'SOAP support missing from PHP.']);
        }

        $matches = [];

        $email = $context->getIn()->getString('email');
        if ($email) {
            try {
                $error = error_reporting();
                error_reporting($error & ~E_WARNING);
                $client = new \Application\DeskPRO\SoapClient\SafeSoapClient($url.'/api?wsdl');
                error_reporting($error);
            } catch (\SoapFault $e) {
                return $context->createJsonResponse(['error' => 'Invalid Zapier URL']);
            }

            try {
                $session = $client->login($user, $key);
            } catch (\SoapFault $e) {
                return $context->createJsonResponse(['error' => 'Invalid Zapier API user or key']);
            }

            $results = $client->call($session, 'customer.list', [
                ['email' => $email],
            ]);

            foreach ($results as $record) {
                $sales = $client->call($session, 'sales_order.list', [
                    ['customer_id' => $record['customer_id']],
                ]);

                $orders = [];
                foreach ($sales as $sale) {
                    $orders[] = [
                        'id'          => $sale['increment_id'],
                        'order_id'    => $sale['order_id'],
                        'created_at'  => $sale['created_at'],
                        'grand_total' => number_format($sale['grand_total'], 2),
                        'currency'    => $sale['order_currency_code'],
                        'status'      => $sale['status'],
                        'url'         => $url.'/admin/sales_order/view/order_id/'.$sale['order_id'].'/',
                    ];
                }

                $matches[] = [
                    'id'      => $record['customer_id'],
                    'name'    => $record['firstname'].' '.$record['lastname'],
                    'email'   => $record['email'],
                    'profile' => $url.'/admin/customer/edit/id/'.$record['customer_id'].'/',
                    'orders'  => $orders,
                ];
            }

            $client->endSession($session);
        }

        return $context->createJsonResponse(['matched' => count($matches), 'matches' => $matches]);
    }
}
