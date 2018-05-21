<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Email\EmailAccount\EditEmailAccount\EditEmailAccount;
use Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\EditEmailAccountType;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\IncomingAccountTester;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Settings\EmailAccountsSettings;
use Application\EmailBundle\Queue\QueueProc;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Env;
use Orb\Validator\StringEmail;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ApiModes("all")
 */
class EmailAccountsController extends AbstractController implements ProtectedControllerInterface
{
    /** @var array|null */
    protected $emailSettings = null;

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
    // list
    //###################################################################################################################

    public function listAction()
    {
        $data = ['email_accounts' => []];

        $manager = $this->container->getEmailAccountManager();
        foreach ($manager->getAllAccounts() as $acc) {
            $data['email_accounts'][] = $acc->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $manager = $this->container->getEmailAccountManager();

        try {
            $account = $manager->getAccount($id);
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException();
        }

        $data['email_account'] = $account->toApiData();

        $trigger = null;
        if ($account) {
            $trigger = $this->em->createQuery('
                SELECT trigger
                FROM DeskPRO:TicketTrigger trigger
                WHERE trigger.email_account = ?0
            ')->setParameters([$account])->getOneOrNullResult();
        }

        if (!$trigger) {
            $trigger = new TicketTrigger();
        }

        $data['trigger'] = $trigger->toApiData();

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        if ($id) {
            $account = $this->container->getEmailAccountManager()->getAccount($id);

            if (!$account) {
                throw $this->createNotFoundException();
            }
        } else {
            $account = new EmailAccount(EmailAccount::TYPE_TICKETS);

            if ($this->settings->get('internal.disable_email_editing.new')) {
                throw $this->createNotFoundException();
            }
        }

        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->getSaveFormData($account);

        if ($data instanceof Response) {
            return $data;
        }

        // Copy gmail config into the transport
        if ($data['incoming_type'] == 'gmail') {
            $data['outgoing_type']     = 'gmail';
            $data['out_gmail_account'] = $data['in_gmail_account'];
        }

        if ($data['incoming_type'] == 'office365') {
            $data['outgoing_type']         = 'office365';
            $data['out_office365_account'] = $data['in_office365_account'];
        }

        $form->submit($data);

        if ($this->settings->get('internal.disable_email_editing.incoming_details')) {
            $edit_account->apply(false);
        } else {
            $edit_account->apply();
        }

        if (!$account->is_all_brands && !$account->brands->count()) {
            return $this->createApiErrorResponse('validation_error', 'Account needs to be linked to at least one Brand');
        }

        $this->em->persist($account);
        $this->em->flush();

        $edit_account->saveTrigger($this->em, $this->in->getArrayValue('trigger_actions'));

        if ($id) {
            return $this->createApiSuccessResponse();
        } else {
            return $this->createApiCreateResponse([
                'email_account_id' => $account->id,
            ], $this->generateUrl('api_emailaccounts_get', ['id' => $account->id]));
        }
    }

    /**
     * @param EmailAccount $account
     *
     * @return array
     */
    protected function getSaveFormData(EmailAccount $account = null)
    {
        return $this->in->getAll('post');
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $account = $this->container->getEmailAccountManager()->getAccount($id);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $accounts = $this->container->getEmailAccountManager()->getAllAccounts();
        if (count($accounts) === 1) {
            return $this->createApiErrorResponse(
                'last_account',
                "You can't delete the last email account. At least one email account is required."
            );
        }

        $old_id = $account->id;
        $this->em->remove($account);
        $this->em->flush();

        return $this->createApiDeleteResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // test-account
    //###################################################################################################################

    public function testAccountAction()
    {
        $account      = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->in->getAll('post');
        $form->submit($data);

        $tester = new IncomingAccountTester(
            EmailAccountUtil::decryptIncomingAccount($edit_account->getIncomingAccountConfig(), $this->container->get('dp_enc')),
            $this->get('settings_resolver')->getGlobalSettings()
        );
        $tester->test();

        return $this->createApiResponse([
            'is_success'    => $tester->isSuccess(),
            'log'           => $tester->getLog(),
            'message_count' => $tester->getMessageCount(),
        ]);
    }

    //###################################################################################################################
    // test-outgoing-account
    //###################################################################################################################

    public function testOutgoingAccountAction()
    {
        $account      = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->getTestOutgoingFormData();
        $form->submit($data);

        if (!StringEmail::isValueValid($this->in->getString('test_email.to'))) {
            return $this->createApiResponse([
                'is_success' => false,
                'log'        => 'Invalid TO email address',
            ]);
        }
        if (!StringEmail::isValueValid($this->in->getString('test_email.from')) || !$this->validateCustomEmailAddress($this->in->getString('test_email.from'))) {
            return $this->createApiResponse([
                'is_success' => false,
                'log'        => 'Invalid FROM email address',
            ]);
        }

        $out_account = $edit_account->getOutgoingAccountConfig();
        if (!$out_account) {
            return $this->createApiResponse([
                'is_success' => false,
                'log'        => 'No outgoing account configuration was specified.',
            ]);
        }

        try {
            $raw_tr = $this->container->get('email.raw_transport_factory')->createTransport(
                EmailAccountUtil::decryptOutgoingAccount($out_account, $this->container->get('dp_enc')),
                $this->get('settings_resolver')->getGlobalSettings()
            );
        } catch (\Exception $e) {
            return $this->createApiResponse([
                'is_success' => false,
                'log'        => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }

        QueueProc::$__dp_current_sendmail = ['id' => '@TEST'];

        $logger = $this->container->get('monolog.logger.dp.email.out.queue');

        $swift_message = \Swift_Message::newInstance()
            ->setSubject($this->in->getString('test_email.subject'))
            ->setBody($this->in->getString('test_email.message'))
            ->setFrom($this->in->getString('test_email.from'))
            ->setTo($this->in->getString('test_email.to'));

        $fp = fopen('php://temp/maxmemory:10000000', 'rw');
        fwrite($fp, $swift_message->toString());

        $logger->info('Begin send test');

        $failed = [];

        try {
            $sent = $raw_tr->sendRawMessage(
            $this->in->getString('test_email.from'),
            [$this->in->getString('test_email.to')],
            $fp,
            $failed
        );

            if ($failed) {
                $logger->notice(sprintf('NOTICE: Failed recipients: %s', implode(', ', $failed)));
            }
        } catch (\Exception $e) {
            $sent = 0;
            $logger->error($e->getMessage());
        }

        $logger->info(sprintf('Sent %d messages', $sent));

        QueueProc::$__dp_current_sendmail = null;

        // Cleans up log a bit to remove channel names
        $log = $this->container->get('email.log_collector')->getLogForMessage('@TEST');
        $log = preg_replace("#^(\[.*?\]) (.*?)\.([A-Z]+): #m", '$1 ', $log);

        return $this->createApiResponse([
            'is_success' => $sent > 0,
            'log'        => $log,
        ]);
    }

    /**
     * @return array
     */
    protected function getTestOutgoingFormData()
    {
        return $this->in->getAll('post');
    }

    public function getSettingsAction()
    {
        if (!$this->emailSettings) {
            $this->emailSettings = new EmailAccountsSettings($this->settings);
        }

        $data = [
            'email_settings' => $this->emailSettings->toArray(),
            'max_filesize'   => Env::getEffectiveMaxUploadSize(),
        ];

        return $this->createApiResponse($data);
    }

    public function setSettingsAction()
    {
        if (!$this->emailSettings) {
            $this->emailSettings = new EmailAccountsSettings($this->settings);
        }

        $data = $this->in->getArrayValue('settings');
        $this->emailSettings->fromArray($data);

        return $this->getSettingsAction();
    }

    /**
     * Hook to validate a custom email address. Overriden on cloud to ensure safe email address.
     *
     * @param string $email
     *
     * @return bool
     */
    protected function validateCustomEmailAddress($email)
    {
        return true;
    }
}
