<?php

namespace DeskPRO\Bundle\VoiceBundle\Model\BillingSummary;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DeskproBillingSummary.
 */
class DeskproBillingSummary
{
    /**
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $totalCallsCount;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $totalCallsPrice;

    /**
     * Constructor.
     *
     * @param $totalCallsCount
     * @param $totalCallsPrice
     */
    public function __construct($totalCallsCount, $totalCallsPrice)
    {
        $this->totalCallsCount = $totalCallsCount;
        $this->totalCallsPrice = number_format($totalCallsPrice, 3, '.', ',');
    }
}
