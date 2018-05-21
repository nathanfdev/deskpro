<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Helper;

use Application\DeskPRO\CustomFields\BillingFieldManager;
use Application\DeskPRO\CustomFields\FieldManager;
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
     * @param $customDataTable
     *
     * @return mixed
     */
    public function getDefTable($customDataTable)
    {
        return str_replace('_data_', '_def_', $customDataTable);
    }

    /**
     * @param $customDataTable
     *
     * @return FieldManager|null
     */
    public function getFieldManager($customDataTable)
    {
        switch ($this->getDefTable($customDataTable)) {
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

        return $manager;
    }

    /**
     * @param string     $customDataTable
     * @param int|string $fieldId
     *
     * @return CustomDefAbstract|null
     */
    public function getCustomField($customDataTable, $fieldId)
    {
        $manager = $this->getFieldManager($customDataTable);

        $field = null;
        if ($manager) {
            $field = $manager->getFieldFromId($fieldId);
        }

        return $field;
    }
}
