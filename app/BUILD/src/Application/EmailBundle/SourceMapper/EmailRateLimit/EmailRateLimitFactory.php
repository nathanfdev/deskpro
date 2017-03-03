<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
        if (!defined('DPC_IS_CLOUD') || (defined('DPC_NO_SENDMAIL_LIMITS') && DPC_NO_SENDMAIL_LIMITS)) {
            return new NullEmailRateLimit();
        }

        $limits = self::getRateLimits();

        if (!$limits) {
            return new NullEmailRateLimit();
        }

        $accountIds = array_map(function ($r) {
            return $r['id'];
        }, $container->get('database_connection')->fetchAll("
            SELECT id
            FROM email_accounts
            WHERE outgoing_account LIKE '%PhpMailConfig%'
        "));

        if (!$accountIds) {
            return new NullEmailRateLimit();
        }

        return new EmailRateLimit(
            $container,
            $limits,
            $accountIds,
            null
        );
    }

    /**
     * @return array
     */
    private static function getRateLimits()
    {
        if (!defined('DPC_IS_CLOUD')) {
            return [];
        }

        if (defined('DPC_SITE_FLAG_DISABLE_OUTMAIL')) {
            return [['time' => 0, 'count' => 0, 'actions' => ['rate_limit']]];
        }

        // Demos
        if (DPC_DEMO_EXPIRE) {
            if (DPC_SITE_IS_SUSPICIOUS) {
                return [['time' => 0, 'count' => 0, 'actions' => ['rate_limit']]];
            }

            if (DPC_SITE_IS_APPROVED) {
                return [
                    ['time' => 900    /* 15m */, 'count' => 40, 'actions' => ['log_account_warning']],
                    ['time' => 3600    /* 1h */, 'count' => min(100, max(DPC_AGENTS * 4, 50)), 'actions' => ['log_account_warning']],
                    ['time' => 28800   /* 1h */, 'count' => 500, 'actions' => ['log_account_warning']],
                ];
            } else {
                return [
                    ['time' => 5       /* 5s */, 'count' => 25, 'actions' => ['cancel_site']],
                    ['time' => 900     /* 15m */, 'count' => 25, 'actions' => ['rate_limit', 'log_suspicious']],
                    ['time' => 86400   /* 24h */, 'count' => 50, 'actions' => ['rate_limit', 'log_suspicious']],
                    ['time' => 1209600 /* 14d */, 'count' => 300, 'actions' => ['rate_limit', 'log_suspicious']],
                ];
            }

        // New accounts (30 days)
        } elseif (DPC_SITE_CREATED_AT > (time() - 3369600)) {
            if (DPC_SITE_IS_SUSPICIOUS) {
                return [['time' => 0, 'count' => 0, 'actions' => ['rate_limit']]];
            }

            if (DPC_SITE_IS_APPROVED) {
                return [
                    ['time' => 900     /* 15m */, 'count' => 40, 'actions' => ['log_account_warning']],
                    ['time' => 3600    /* 1h */, 'count' => min(100, max(DPC_AGENTS * 4, 50)), 'actions' => ['log_account_warning']],
                ];
            } else {
                return [
                    ['time' => 5       /* 5s */, 'count' => 30, 'actions' => ['cancel_site']],
                    ['time' => 900     /* 15m */, 'count' => 40, 'actions' => ['log_account_warning']],
                    ['time' => 3600    /* 1h */, 'count' => min(100, max(DPC_AGENTS * 4, 50)), 'actions' => ['rate_limit', 'log_suspicious']],
                ];
            }

        // Everyone else
        } else {
            return [];
        }
    }
}
