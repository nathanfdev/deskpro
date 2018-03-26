<?php

/**
 * DeskPRO.
 */

namespace Cloud\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\EmailAccount;
use Application\LegacyApiBundle\Controller\EmailAccountsController as BaseEmailAccountsController;
use DeskPRO\Component\Util\StringUtils;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;

class EmailAccountsController extends BaseEmailAccountsController
{
    /**
     * @param EmailAccount $account
     *
     * @return array
     */
    protected function getSaveFormData(EmailAccount $account = null)
    {
        $data = parent::getSaveFormData($account);

        // Give a default value to address_name if it was left blank
        if (empty($data['address_name']) || !$data['address_name']) {
            $db    = $this->container->getDb();
            $check = function ($addy) use ($db) {
                return $db->fetchColumn('SELECT COUNT(*) FROM email_accounts WHERE address = ?', [$addy]);
            };
            $count = 0;
            do {
                $data['address_name'] = 'contact'.($count ? $count : '').'@'.DPC_SITE_DOMAIN;
                ++$count;
            } while ($check($data['address_name']) > 0);
        }

        if ($data['account_type'] == 'tickets') {
            $data['address'] = $data['address_name'].'@'.DPC_SITE_DOMAIN;

            $data['other_addresses'] = explode(',', @$data['other_addresses'] ?: '');
            $data['other_addresses'] = Arrays::func($data['other_addresses'], 'trim');
            $data['other_addresses'] = Arrays::removeFalsey($data['other_addresses']);

            if ($account && $cust = $account->getOption('custom_email_address')) {
                $data['other_addresses'] = array_filter($data['other_addresses'], function ($x) use ($cust) {
                    return $x != $cust;
                });
            }

            $invalidEmails = [];
            foreach ($data['other_addresses'] as $e) {
                if (!$this->validateCustomEmailAddress($e)) {
                    $invalidEmails[] = $e;
                }
            }

            // All custom emails are also aliases
            if ($data['use_custom_email_address'] && !empty($data['custom_email_address'])) {
                array_unshift($data['other_addresses'], $data['custom_email_address']);
                if ($account) {
                    $account->setOption('custom_email_address', $data['custom_email_address']);
                }
                if (!$this->validateCustomEmailAddress($data['custom_email_address'])) {
                    $invalidEmails[] = $data['custom_email_address'];
                }
            } else {
                $data['use_custom_email_address'] = false;
                if ($account) {
                    $account->setOption('custom_email_address', null);
                }
            }

            if ($invalidEmails) {
                return $this->createApiErrorResponse('invalid_data', 'The following email addresses are invalid: '.implode(', ', $invalidEmails));
            }

            $data['other_addresses'] = implode(',', $data['other_addresses']);

            // If not using a custom address, then outgoing account should always be mail
            if (!$data['use_custom_email_address']) {
                $data['outgoing_type'] = 'php_mail';
            }

            $data['out_gmail_account']['user'] = !empty($data['custom_email_address']) ? $data['custom_email_address'] : $data['address'];

            // All incoming types are 'noop' because they aren't actually processed in the same way on cron
            $data['incoming_type'] = 'noop';
        } elseif ($data['account_type'] == 'outgoing') {
            throw $this->createNotFoundException();
        } else {
            throw $this->createNotFoundException();
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTestOutgoingFormData()
    {
        return $this->getSaveFormData();
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function validateCustomEmailAddress($email)
    {
        $email = strtolower(trim($email));

        if (preg_match('/@deskpro\.[a-z]+$/', $email)) {
            return false;
        }

        if (preg_match('/@\w+\.deskpro\.[a-z]+$/', $email) && !StringUtils::endsWith('@'.DPC_SITE_DOMAIN, $email)) {
            return false;
        }

        return StringEmail::isValueValid($email) && !StringEmail::isExampleEmail($email);
    }
}
