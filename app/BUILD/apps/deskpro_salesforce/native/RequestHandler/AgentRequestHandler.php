<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_salesforce\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;
use Application\DeskPRO\Entity\DataStore;
use DpSys\LowError\SystemErrorHandler;

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
        libxml_disable_entity_loader(false);
        $user     = $context->getAppSetting('api_user');
        $password = $context->getAppSetting('api_password');
        $token    = $context->getAppSetting('api_security_token');

        if (!$user || !$password || !$token) {
            return $context->createJsonResponse(['error' => 'API user, password or token missing. Please configure the plugin.']);
        }

        $matches = [];

        $email = $context->getIn()->getString('email');
        if ($email) {
            try {
                $error = error_reporting();
                error_reporting($error & ~E_WARNING);

                $old = libxml_disable_entity_loader(false);
                require_once DP_ROOT.'/vendor-src/salesforce/SforcePartnerClient.php';
                $sforce = new \SforcePartnerClient();
                $sforce->createConnection(DP_ROOT.'/vendor-src/salesforce/partner.wsdl.xml');
                libxml_disable_entity_loader($old);

                error_reporting($error);
            } catch (\SoapFault $e) {
                return $context->createJsonResponse(['error' => 'Invalid Salesforce URL.']);
            }

            try {
                $sforce->login($user, $password.$token);
            } catch (\SoapFault $e) {
                if ($e->getMessage()) {
                    return $context->createJsonResponse(['error' => 'Salesforce error: '.$e->getMessage()]);
                }

                return $context->createJsonResponse(['error' => 'Invalid Salesforce API user, password, or token.']);
            }

            $fields = $this->getFields($context, $sforce);

            try {
                $matches = $this->lookupUsers($email, $fields, $context, $sforce);
            } catch (\SoapFault $e) {
                // If its an invalid field error, someone could have changed fields within
                // sf so one is now invlaid. so force a refresh of the cache then try again
                if ($e->getMessage() == 'INVALID_FIELD') {
                    $fields  = $this->getFields($context, $sforce, true);
                    $matches = $this->lookupUsers($email, $fields, $context, $sforce);
                } else {
                    throw $e;
                }
            }
        }

        return $context->createJsonResponse([
            'matches' => $matches,
        ]);
    }

    /**
     * @param string               $email
     * @param array                $fields
     * @param AgentRequestContext  $context
     * @param \SforcePartnerClient $sforce
     *
     * @return array
     */
    private function lookupUsers($email, $fields, AgentRequestContext $context, \SforcePartnerClient $sforce)
    {
        $matches = [];

        $fields_list = implode(', ', $fields);

        try {
            $response = $sforce->query("
                SELECT $fields_list
                FROM Contact
                WHERE Email = '".addslashes($email)."'
            ");
        } catch (\Exception $e) {
            $response = null;
            SystemErrorHandler::logException($e, false, 'salesforce_'.$e->getMessage());
        }

        if ($response) {
            foreach ($response->records as $record) {
                if (@$record->fields->Title && @$record->fields->Department) {
                    $departmentTitle = @$record->fields->Department.', '.@$record->fields->Title;
                } else {
                    $departmentTitle = @$record->fields->Department.@$record->fields->Title;
                }

                $matches[] = [
                    'id'              => $record->Id,
                    'name'            => @$record->fields->FirstName.' '.@$record->fields->LastName,
                    'email'           => @$record->fields->Email,
                    'title'           => @$record->fields->Title,
                    'department'      => @$record->fields->Department,
                    'departmentTitle' => @$departmentTitle,
                    'profile'         => 'https://na8.salesforce.com/'.$record->Id,
                ];
            }
        }

        return $matches;
    }

    /**
     * @param AgentRequestContext  $context
     * @param \SforcePartnerClient $sforce
     * @param bool                 $force_reset
     *
     * @return array
     */
    private function getFields(AgentRequestContext $context, \SforcePartnerClient $sforce, $force_reset = false)
    {
        $data_id = 'apps.'.$context->getApp()->id.'.fields';

        $data = $context->getEm()->getRepository('DeskPRO:DataStore')->getByName($data_id);
        if ($force_reset || !$data || $data->getData('ts_created') < time() - 28800) {
            $data = null;
        }

        if (!$data) {
            $fields = [];

            $desc = $sforce->describeSObject('Contact');

            foreach ($desc->fields as $f) {
                switch ($f->name) {
                    case 'Id':
                        $fields[] = 'Id';
                        break;
                    case 'FirstName':
                        $fields[] = 'FirstName';
                        break;
                    case 'LastName':
                        $fields[] = 'LastName';
                        break;
                    case 'Email':
                        $fields[] = 'Email';
                        break;
                    case 'Title':
                        $fields[] = 'Title';
                        break;
                    case 'Department':
                        $fields[] = 'Department';
                        break;
                }
            }

            $context->getDb()->delete('datastore', ['name' => $data_id]);

            $data       = new DataStore();
            $data->name = $data_id;
            $data->setData('fields', $fields);
            $data->setData('ts_created', time());
            $context->getEm()->persist($data);
            $context->getEm()->flush($data);
        }

        return $data->getData('fields', []);
    }
}
