<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;

/**
 * Class AbstractLegacyFiltersController.
 */
abstract class AbstractLegacyFiltersController extends BaseController
{
    /**
     * @param int $id
     *
     * @return \DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSet
     */
    protected function getFilterSetOr404($id)
    {
        $filterSet = $this->getLegacyFilterSetService()->getFilterSet($id);
        if (!$filterSet) {
            throw $this->createNotFoundException();
        }

        return $filterSet;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSetDataService
     */
    protected function getLegacyFilterSetService()
    {
        return $this->get('data.ticket_legacy_filter_sets');
    }
}
