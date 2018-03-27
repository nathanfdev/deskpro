<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig;
use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Component\Util\ListUtils;
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

        $accounts = $container->get('email.email_account_manager')->getAllAccounts('with_transport');
        $accounts = ListUtils::filter($accounts, function (EmailAccount $ac) {
            return !($ac->getOutgoingAccount() instanceof SmtpConfig);
        });

        $accountIds = ListUtils::map($accounts, function (EmailAccount $ac) {
            return $ac->getId();
        });

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
                    ['time' => 5       /* 5s */, 'count' => 25, 'actions' => ['rate_limit', 'log_suspicious']],
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
                    ['time' => 5       /* 5s */, 'count' => 30, 'actions' => ['log_account_warning']],
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
