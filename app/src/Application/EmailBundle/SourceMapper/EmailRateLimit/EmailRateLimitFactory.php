<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Symfony\Component\DependencyInjection\Container;

class EmailRateLimitFactory
{
    /**
     * @param Container $container
     *
     * @return EmailRateLimitInterface
     */
    public static function create(Container $container)
    {
        /** @var \Doctrine\DBAL\Connection $db */
        $db             = $container->getDb();
        $dp_account_ids = null;
        if (defined('DPC_IS_CLOUD') && DPC_DEMO_EXPIRE) {
            $dp_account_ids = array_map(function ($r) { return $r['id']; }, $db->fetchAll("
                SELECT id
                FROM email_accounts
                WHERE outgoing_account LIKE '%PhpMailConfig%'
            "));
        }

        // Demo clouds have rate limits on our accounts
        if (defined('DPC_IS_CLOUD') && DPC_DEMO_EXPIRE && $dp_account_ids) {
            $reset_datetime = null;

            if ($container instanceof DeskproContainer) {
                $reset_setting = $container->getSetting('cloud.email_out.limits.reset_date');

                if ($reset_setting) {
                    try {
                        $reset_datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $reset_setting);
                    } catch (\Exception $e) {
                    }
                }
            }

            $limits = array(
                24  => 50,
                336 => 300,
            );

            return new EmailRateLimit(
                $container->getEm()->getRepository('EmailBundle:SendmailSource'),
                $limits,
                $dp_account_ids,
                $reset_datetime
            );
        } else {
            return new NullEmailRateLimit();
        }
    }
}
