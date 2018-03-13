<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Helper;

use Application\DeskPRO\CustomFields\BillingFieldManager;
use Application\DeskPRO\CustomFields\OrganizationFieldManager;
use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;
use Application\DeskPRO\Entity\CustomDefAbstract;

/**
 * Class CustomDataHelper.
 */
class CustomDataHelper
{
    /**
     * @var TicketFieldManager
     */
    private $ticketFieldManager;

    /**
     * @var BillingFieldManager
     */
    private $billingFieldManager;

    /**
     * @var PersonFieldManager
     */
    private $personFieldManager;

    /**
     * @var OrganizationFieldManager
     */
    private $orgFieldManager;

    /**
     * Constructor.
     *
     * @param TicketFieldManager       $ticketFieldManager
     * @param BillingFieldManager      $billingFieldManager
     * @param PersonFieldManager       $personFieldManager
     * @param OrganizationFieldManager $orgFieldManager
     */
    public function __construct(
        TicketFieldManager       $ticketFieldManager,
        BillingFieldManager      $billingFieldManager,
        PersonFieldManager       $personFieldManager,
        OrganizationFieldManager $orgFieldManager
    ) {
        $this->ticketFieldManager  = $ticketFieldManager;
        $this->billingFieldManager = $billingFieldManager;
        $this->personFieldManager  = $personFieldManager;
        $this->orgFieldManager     = $orgFieldManager;
    }

    /**
     * @param string     $customDataTable
     * @param int|string $fieldId
     *
     * @return CustomDefAbstract|null
     */
    public function getCustomField($customDataTable, $fieldId)
    {
        $customDefTable = str_replace('_data_', '_def_', $customDataTable);
        switch ($customDefTable) {
            case 'custom_def_ticket':
                $manager = $this->ticketFieldManager;
                break;
            case 'custom_def_billing':
                $manager = $this->billingFieldManager;
                break;
            case 'custom_def_people':
                $manager = $this->personFieldManager;
                break;
            case 'custom_def_organizations':
                $manager = $this->orgFieldManager;
                break;
            default:
                $manager = null;
                break;
        }

        $field = null;
        if ($manager) {
            $field = $manager->getFieldFromId($fieldId);
        }

        return $field;
    }
}
