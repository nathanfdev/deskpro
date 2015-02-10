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
use Application\DeskPRO\Email\EmailAccount\EditEmailAccount\EditEmailAccount;
use Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\EditEmailAccountType;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\IncomingAccountTester;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\OutgoingAccountTester;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Settings\EmailAccountsSettings;
use Orb\Util\Env;
use Orb\Validator\StringEmail;

class EmailAccountsController extends AbstractController implements ProtectedControllerInterface
{
    /** @var array|null */
    protected $emailSettings = null;

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
    # list
    ####################################################################################################################

    public function listAction()
    {
        $data = array('email_accounts' => array());

        $manager = $this->container->getEmailAccountManager();
        foreach ($manager->getAllAccounts() as $acc) {
            $data['email_accounts'][] = $acc->toApiData();
        }

        return $this->createApiResponse($data);
    }


    ####################################################################################################################
    # get
    ####################################################################################################################

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
            $trigger = $this->em->createQuery("
                SELECT trigger
                FROM DeskPRO:TicketTrigger trigger
                WHERE trigger.email_account = ?0
            ")->setParameters(array($account))->getOneOrNullResult();
        }

        if (!$trigger) {
            $trigger = new TicketTrigger();
        }

        $data['trigger'] = $trigger->toApiData();

        return $this->createApiResponse($data);
    }


    ####################################################################################################################
    # save
    ####################################################################################################################

    public function saveAction($id)
    {
        if ($id) {
            $account = $this->container->getEmailAccountManager()->getAccount($id);

            if (!$account) {
                throw $this->createNotFoundException();
            }
        } else {
            $account = new EmailAccount(EmailAccount::TYPE_TICKETS);
        }

        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->getSaveFormData($account);

        // Copy gmail config into the transport
        if ($data['incoming_type'] == 'gmail') {
            $data['outgoing_type']     = 'gmail';
            $data['out_gmail_account'] = $data['in_gmail_account'];
        }

        if ($data['incoming_type'] == 'office365') {
            $data['outgoing_type']     = 'office365';
            $data['out_office365_account'] = $data['in_office365_account'];
        }

        $form->submit($data);
        $edit_account->apply();

        $this->em->persist($account);
        $this->em->flush();

        $edit_account->saveTrigger($this->em, $this->in->getArrayValue('trigger_actions'));

        if ($id) {
            return $this->createApiSuccessResponse();
        } else {
            return $this->createApiCreateResponse(array(
                'email_account_id' => $account->id,
            ), $this->generateUrl('api_emailaccounts_get', array('id' => $account->id)));
        }
    }


    /**
     * @param  EmailAccount $account
     * @return array
     */
    protected function getSaveFormData(EmailAccount $account = null)
    {
        return $this->in->getAll('post');
    }


    ####################################################################################################################
    # remove
    ####################################################################################################################

    public function removeAction($id)
    {
        $account = $this->container->getEmailAccountManager()->getAccount($id);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $old_id = $account->id;
        $this->em->remove($account);
        $this->em->flush();

        return $this->createApiDeleteResponse(array('old_id' => $old_id));
    }


    ####################################################################################################################
    # test-account
    ####################################################################################################################

    public function testAccountAction()
    {
        $account = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->in->getAll('post');
        $form->submit($data);

        $tester = new IncomingAccountTester($edit_account->getIncomingAccountConfig());
        $tester->test();

        return $this->createApiResponse(array(
            'is_success'    => $tester->isSuccess(),
            'log'           => $tester->getLog(),
            'message_count' => $tester->getMessageCount()
        ));
    }

    ####################################################################################################################
    # test-outgoing-account
    ####################################################################################################################

    public function testOutgoingAccountAction()
    {
        $account = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $edit_account = new EditEmailAccount($account);

        $form = $this->createForm(
            new EditEmailAccountType(),
            $edit_account
        );

        $data = $this->getTestOutgoingFormData();
        $form->submit($data);

        if (!StringEmail::isValueValid($this->in->getString('test_email.to'))) {
            return $this->createApiResponse(array(
                'is_success'    => false,
                'log'           => 'Invalid TO email address',
            ));
        }
        if (!StringEmail::isValueValid($this->in->getString('test_email.from'))) {
            return $this->createApiResponse(array(
                'is_success'    => false,
                'log'           => 'Invalid FROM email address',
            ));
        }

        $out_account = $edit_account->getOutgoingAccountConfig();
        if (!$out_account) {
            return $this->createApiResponse(array(
                'is_success'    => false,
                'log'           => 'No outgoing account configuration was specified.',
            ));
        }

        $tester = new OutgoingAccountTester($out_account);
        $tester->test(
            $this->in->getString('test_email.to'),
            $this->in->getString('test_email.from'),
            $this->in->getString('test_email.subject'),
            $this->in->getString('test_email.message')
        );

        return $this->createApiResponse(array(
            'is_success'    => $tester->isSuccess(),
            'log'           => $tester->getLog(),
        ));
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

        $data = array(
            'email_settings' => $this->emailSettings->toArray(),
            'max_filesize'     => Env::getEffectiveMaxUploadSize(),
        );

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
}
