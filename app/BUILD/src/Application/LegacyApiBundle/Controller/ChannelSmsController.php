<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\SmsAccount;
use Application\DeskPRO\Sms\SmsProviderFactory;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;
use Orb\Util\DpStrings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ChannelSmsController.
 *
 * @ApiModes("all")
 */
class ChannelSmsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list sms accounts
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $accounts = $this->getSmsAccountRepo()->findAll();

        $data = $this->getContainer()->getSerializer()->serializeArray($accounts);

        return $this->createApiResponse(['sms_accounts' => $data]);
    }

    //###################################################################################################################
    // get sms account
    //###################################################################################################################

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        $account = $this->getSmsAccountRepo()->find($id);

        if (!$account) {
            return $this->createApiErrorResponse('not_found', sprintf('sms account (id=%s) does not exist', $id));
        }

        $data = $this->getContainer()->getSerializer()->serialize($account);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save sms account
    //###################################################################################################################

    /**
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        /*
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
                [
                    'account' => $serializedAccount,
                ]);
        } else {
            return $this->createApiCreateResponse(
                [
                    'account' => $serializedAccount,
                ], $this->generateUrl('api_channel_sms_account_get', ['id' => $account->id])
            );
        }
    }

    //###################################################################################################################
    // connect to a provider and return provider specific info
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function connectProviderAction()
    {
        $accountData = $this->in->getValue('account');
        $id          = $this->in->getValue('account.id');

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
                $accountData['params'] = [];
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

            return $this->createApiSuccessResponse(['account' => $accountData]);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse(
                'sms.connection_error', 'Could not connect. Please check your credentials'
            );
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        $account->is_tested  = false;
        $account->is_enabled = false;
        $account->test_code  = DpStrings::random(8);

        $this->saveSmsAccount($account);

        // setup twilio endpoint
        $twilio_endpoint = $this->generateUrl('api_channel_incoming_sms_twilio', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $provider        = SmsProviderFactory::create($account->type, $account->params);
        $provider->setUrlForNumber($twilio_endpoint, $account->phone_number->number);

        // send a text
        $sender = new SmsSender($provider, $account->phone_number->number);
        $sender->send($account->phone_number->number, $msg = new SmsMessage($account->test_code));

        return $this->createApiSuccessResponse();
    }

    //###################################################################################################################
    // delete sms accounts
    //###################################################################################################################

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
