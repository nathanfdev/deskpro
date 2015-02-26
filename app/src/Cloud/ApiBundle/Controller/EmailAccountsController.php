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

namespace Cloud\ApiBundle\Controller;

use Application\ApiBundle\Controller\EmailAccountsController as BaseEmailAccountsController;
use Application\DeskPRO\Entity\EmailAccount;
use Orb\Util\Arrays;

class EmailAccountsController extends BaseEmailAccountsController
{
    /**
     * @return array
     */
    protected function getSaveFormData(EmailAccount $account = null)
    {
        $data = parent::getSaveFormData($account);

        // Give a default value to address_name if it was left blank
        if (empty($data['address_name']) || !$data['address_name']) {
            $db = $this->container->getDb();
            $check = function ($addy) use ($db) {
                return $db->fetchColumn("SELECT COUNT(*) FROM email_accounts WHERE address = ?", array($addy));
            };
            $count = 0;
            do {
                $data['address_name'] = 'contact' . ($count ? $count : '') . '@' . DPC_SITE_DOMAIN;
                $count++;
            } while ($check($data['address_name']) > 0);
        }

        if ($data['account_type'] == 'tickets') {

            $data['address'] = $data['address_name'] . '@' . DPC_SITE_DOMAIN;

            $data['other_addresses'] = explode(',', @$data['other_addresses'] ?: '');
            $data['other_addresses'] = Arrays::func($data['other_addresses'], 'trim');
            $data['other_addresses'] = Arrays::removeFalsey($data['other_addresses']);

            if ($account && $cust = $account->getOption('custom_email_address')) {
                $data['other_addresses'] = array_filter($data['other_addresses'], function ($x) use ($cust) { return $x != $cust; });
            }

            // All custom emails are also aliases
            if ($data['use_custom_email_address'] && !empty($data['custom_email_address'])) {
                array_unshift($data['other_addresses'], $data['custom_email_address']);
                if ($account) {
                    $account->setOption('custom_email_address', $data['custom_email_address']);
                }
            } else {
                $data['use_custom_email_address'] = false;
                if ($account) {
                    $account->setOption('custom_email_address', null);
                }
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
}
