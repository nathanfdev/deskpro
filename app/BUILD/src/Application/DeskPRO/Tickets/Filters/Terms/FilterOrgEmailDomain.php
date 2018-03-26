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
 * Filters based on ticket org email domain.
 *
 * @option string domain
 */
class FilterOrgEmailDomain extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('domain');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options = $this->getTermOptions();

        $query = $this->getStringMatchQuery('org_email_domain.domain', $options['domain']);
        $query->addJoin('tickets.organization.email_domains', 'organization_email_domains', 'org_email_domain', 'org_email_domain.organization_id = tickets.organization_id');

        return $query;
    }
}
