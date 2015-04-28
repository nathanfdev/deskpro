<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\SmsAccount;
use Application\DeskPRO\Sms\SmsProviderFactory;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChannelSmsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }


    ####################################################################################################################
    # list sms accounts
    ####################################################################################################################

    public function listAction()
    {
        $accounts = $this->getSmsAccountRepo()->findAll();

        $data = $this->getContainer()->getSerializer()->serializeArray($accounts);

        return $this->createApiResponse(array('sms_accounts' => $data));
    }


    ####################################################################################################################
    # get sms account
    ####################################################################################################################

    public function getAction($id)
    {
        $account = $this->getSmsAccountRepo()->find($id);

        if (!$account) {
            return $this->createApiErrorResponse('not_found', sprintf('sms account (id=%s) does not exist', $id));
        }

        $data = $this->getContainer()->getSerializer()->serialize($account);

        return $this->createApiResponse($data);
    }


    ####################################################################################################################
    # save sms account
    ####################################################################################################################

    public function saveAction($id = null)
    {
        if ($id) {
            $account = $this->getContainer()->getEm()->getRepository('DeskPRO:SmsAccount')->find($id);

            if (!$account) {
                return $this->createApiErrorResponse('sms.account_not_found', 'sms account not found', 404);
            }
        } else {
            $account = new SmsAccount();
        }


        /**
         * If anything needs to be done with the data here in the future, a Form should be made
         * on an EditSmsAccount object
         */
        if (!$account->phone_number) {
            $account->phone_number = new PhoneNumber();
        }
        $account->type                 = $this->in->getValue('account.type');
        $account->params               = $this->in->getValue('account.params');
        $account->identifier           = $this->in->getValue('account.identifier');
        $account->phone_number->number = $this->in->getValue('account.phone_number');
        $account->is_enabled           = $this->in->getValue('account.is_enabled');
        $account->is_tested            = $this->in->getValue('account.is_tested');
        $account->is_connected         = $this->in->getValue('account.is_connected');

        $this->saveSmsAccount($account);

        $serializedAccount = $this->getContainer()->getSerializer()->serialize($account);

        if ($id) {
            return $this->createApiSuccessResponse(
                array(
                    'account' => $serializedAccount
                ));
        } else {
            return $this->createApiCreateResponse(
                array(
                    'account' => $serializedAccount
                ), $this->generateUrl('api_channel_sms_account_get', array('id' => $account->id))
            );
        }
    }


    ####################################################################################################################
    # connect to a provider and return provider specific info
    ####################################################################################################################

    public function connectProviderAction()
    {
        $accountData = $this->in->getValue('account');
        $id = $this->in->getValue('account.id');

        $account = null;
        if ($id) {
            $account = $this->getSmsAccountRepo()->find($id);
        }

        try {
            $provider = SmsProviderFactory::create(
                $this->in->getValue('account.type'), $this->in->getValue('account.params')
            );
        } catch (\InvalidArgumentException $e) {
            return $this->createApiErrorResponse('sms.connection_error', 'Invalid SMS account type');
        }


        try {
            $data = $provider->getIncomingNumbers();
            $name = $provider->getAccountName();
            if (!isset($accountData['params'])) {
                $accountData['params'] = array();
            }
            $accountData['params']['numbers'] = $data;
            $accountData['identifier']        = $name;
            $accountData['is_connected']      = true;

            // update the SmsAccount with new "synced" data
            if ($account) {
                $account->params       = $accountData['params'];
                $account->identifier   = $accountData['identifier'];
                $account->is_connected = $accountData['is_connected'];
                $this->getContainer()->getEm()->persist($account);
                $this->getContainer()->getEm()->flush();
            }

            return $this->createApiSuccessResponse(array('account' => $accountData));
        } catch (\Exception $e) {
            return $this->createApiErrorResponse(
                'sms.connection_error', 'Could not connect. Please check your credentials'
            );
        }

    }


    public function setupAndTestTwilioAction()
    {
        $request = $this->request;

        $accountData = $this->in->getValue('account');
        $id          = $this->in->getValue('account.id');

        $account = null;
        if ($id) {
            $account = $this->getSmsAccountRepo()->find($id);
        }

        if (!$account) {
            $this->createApiErrorResponse('invalid', 'no sms account found');
        }

        $account->is_tested = false;
        $account->is_enabled = false;
        $account->test_code = DpStrings::random(8);

        $this->saveSmsAccount($account);

        // setup twilio endpoint
        $twilio_endpoint = $this->generateUrl('api_channel_incoming_sms_twilio', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $provider = SmsProviderFactory::create($account->type, $account->params);
        $provider->setUrlForNumber($twilio_endpoint, $account->phone_number->number);

        // send a text
        $sender = new SmsSender($provider, $account->phone_number->number);
        $sender->send($account->phone_number->number, $msg = new SmsMessage($account->test_code));

        return $this->createApiSuccessResponse();
    }


    ####################################################################################################################
    # delete sms accounts
    ####################################################################################################################

    public function deleteAction($id)
    {
        $account = $this->getSmsAccountRepo()->find($id);

        if (!$account) {
            return $this->createApiErrorResponse('not_found', sprintf('sms account (id=%s) does not exist', $id));
        }

        $em = $this->getContainer()->getEm();
        $em->remove($account);
        $em->flush();

        return $this->createApiSuccessResponse();
    }


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getSmsAccountRepo()
    {
        return $this->getContainer()->getEm()->getRepository('DeskPRO:SmsAccount');
    }

    /**
     * @param $account
     */
    protected function saveSmsAccount(SmsAccount $account)
    {
        $this->getContainer()->getEm()->persist($account);
        $this->getContainer()->getEm()->flush();
    }
}
