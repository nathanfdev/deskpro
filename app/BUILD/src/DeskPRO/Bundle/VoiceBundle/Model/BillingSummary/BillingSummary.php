<?php

namespace DeskPRO\Bundle\VoiceBundle\Model\BillingSummary;

use JMS\Serializer\Annotation as JMS;

/**
 * Class BillingSummary.
 */
class BillingSummary
{
    /**
     * @JMS\Type("DeskPRO\Bundle\VoiceBundle\Model\BillingSummary\DeskproBillingSummary")
     *
     * @var DeskproBillingSummary
     */
    private $deskproStat;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\VoiceBundle\Model\BillingSummary\ProviderBillingSummaryRecord>")
     *
     * @var ProviderBillingSummaryRecord[]
     */
    private $providerStatRecords;

    /**
     * Constructor.
     *
     * @param DeskproBillingSummary $deskproStat
     */
    public function __construct(DeskproBillingSummary $deskproStat)
    {
        $this->deskproStat = $deskproStat;
    }

    /**
     * @param ProviderBillingSummaryRecord[] $providerStatRecords
     */
    public function setProviderStatRecords(array $providerStatRecords)
    {
        $this->providerStatRecords = $providerStatRecords;
    }
}
