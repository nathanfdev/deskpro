<?php

namespace DeskPRO\Bundle\VoiceBundle\Model\BillingSummary;

use JMS\Serializer\Annotation as JMS;
use Twilio\Rest\Api\V2010\Account\Usage\RecordInstance;

/**
 * Class ProviderBillingSummaryRecord.
 */
class ProviderBillingSummaryRecord
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $category;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $count;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $countUnit;

    /**
     * @JMS\Type("string")
     *
     * @var float
     */
    private $price;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $priceUnit;

    /**
     * Constructor.
     *
     * @param RecordInstance $record
     */
    public function __construct(RecordInstance $record)
    {
        $this->category    = $record->category;
        $this->description = $record->description;
        $this->count       = $record->count;
        $this->countUnit   = $record->countUnit;
        $this->price       = number_format($record->price, 3, '.', ',');
        $this->priceUnit   = $record->priceUnit;
    }
}
