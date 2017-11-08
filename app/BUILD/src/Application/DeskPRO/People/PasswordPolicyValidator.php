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

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\PasswordHistory as PasswordHistoryRepos;
use Application\DeskPRO\Settings\PasswordPolicy;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Session\Session;

class PasswordPolicyValidator
{
    /**
     * Magic value used when we want to mark the password as expired
     * right away (e.g., an admin-set password).
     *
     * This will cause a expired to trigger even if the policy
     * doesnt have a password expiry.
     */
    const MAGIC_PASSWORD_EXPIRED_TRIGGER_DATE = '1995-01-23 12:34:55';

    /**
     * @var \Application\DeskPRO\Settings\PasswordPolicy
     */
    private $user_policy;

    /**
     * @var \Application\DeskPRO\Settings\PasswordPolicy
     */
    private $agent_policy;

    /**
     * @var \Application\DeskPRO\EntityRepository\PasswordHistory
     */
    private $history_repos;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param PasswordPolicy       $user_policy
     * @param PasswordPolicy       $agent_policy
     * @param PasswordHistoryRepos $history_repos
     */
    public function __construct(PasswordPolicy $user_policy, PasswordPolicy $agent_policy, PasswordHistoryRepos $history_repos, Session $session)
    {
        $this->user_policy   = $user_policy;
        $this->agent_policy  = $agent_policy;
        $this->history_repos = $history_repos;
        $this->session       = $session;
    }

    /**
     * @param string $password The password to check
     * @param Person $person   The user to check on
     * @param string $error
     *
     * @return bool
     */
    public function checkPassword($password, Person $person = null, &$error = null)
    {
        $policy = $this->getPolicy($person);

        if ($policy->min_length && Strings::utf8_strlen($password) < $policy->min_length) {
            $error = 'min_length';

            return false;
        }

        $m = null;
        if ($policy->require_num_uppercase && preg_match_all('#[A-Z]#', $password, $m) < $policy->require_num_uppercase) {
            $error = 'require_num_uppercase';

            return false;
        }

        $m = null;
        if ($policy->require_num_lowercase && preg_match_all('#[a-z]#', $password, $m) < $policy->require_num_lowercase) {
            $error = 'require_num_lowercase';

            return false;
        }

        $m = null;
        if ($policy->require_num_number && preg_match_all('#[0-9]#', $password, $m) < $policy->require_num_number) {
            $error = 'require_num_number';

            return false;
        }

        $m = null;
        if ($policy->require_num_symbol && preg_match_all('#[\-@£€!$%^&*()_+|~=`{}\[\]:";\'<>?,./\#]#', $password, $m) < $policy->require_num_symbol) {
            $error = 'require_num_symbol';

            return false;
        }

        if ($policy->forbid_reuse && $person && $person->id && $this->history_repos->isUsedPassword($person, $password)) {
            $error = 'forbid_reuse';

            return false;
        }

        return true;
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function isPasswordExpired(Person $person)
    {
        if (!$person->date_password_set) {
            return false;
        }

        if ($this->session->get('auth_by') !== 'Application\DeskPRO\Usersource\Adapter\DeskPRO') {
            return false;
        }

        // Matches special expired date
        if ($person->date_password_set->format('Y-m-d H:i:s') === self::MAGIC_PASSWORD_EXPIRED_TRIGGER_DATE) {
            return true;
        }

        if ($person->is_agent) {
            $policy = $this->agent_policy;
        } else {
            $policy = $this->user_policy;
        }

        if (!$policy->max_age) {
            return false;
        }

        $days = floor((time() - $person->date_password_set->getTimestamp()) / 86400);

        return $days > $policy->max_age && ($person->hasDeskproUsersource('user') || $person->hasDeskproUsersource('agent'));
    }

    public function getPolicy(Person $person)
    {
        if ($person->is_agent) {
            return $this->agent_policy;
        } else {
            return $this->user_policy;
        }
    }
}
