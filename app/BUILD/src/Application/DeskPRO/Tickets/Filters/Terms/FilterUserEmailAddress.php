<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on ticket user email address.
 *
 * If the first character of 'email' is the at-symbol, we automatically search against the email domain.
 *
 * @option string email
 */
class FilterUserEmailAddress extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('email');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options = $this->getTermOptions();

        $email     = $options['email'];
        $is_domain = $email[0] === '@';

        if ($is_domain) {
            $query = $this->getStringMatchQuery('user_email.email_domain', $email);
        } else {
            $query = $this->getStringMatchQuery('user_email.email', $email);
        }

        $query->addJoin('tickets.person.email', 'people_emails', 'user_email', 'user_email.person_id = tickets.person_id');

        return $query;
    }
}
